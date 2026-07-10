<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operations\Caja;
use App\Models\Operations\CajaMensualidad;
use App\Models\Operations\CajaMovimiento;
use App\Models\Identity\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CajaController extends Controller
{
    private function checkSuperadmin()
    {
        return session('es_superadmin') === true;
    }

    private function checkAccess()
    {
        $esSuperadmin = $this->checkSuperadmin();

        $rolNombre = mb_strtolower(trim((string) (auth()->user()?->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) (auth()->user()?->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $esAdmin = in_array($rolNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);

        $sucursalId = (int) session('sucursal_id', 0);

        // Acceso permitido solo a Superadmin o Admin de sucursal 1 (Novitec Quito / UIO)
        return $esSuperadmin || ($esAdmin && $sucursalId === 1);
    }

    public function movimientos(Request $request)
    {
        if (!$this->checkAccess()) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        $esSuper = $this->checkSuperadmin();

        // Obtener las cajas globales
        $cajaChica = Caja::firstOrCreate(['tipo' => 'chica'], ['sucursal_id' => 1]);
        $cajaGrande = Caja::firstOrCreate(['tipo' => 'grande'], ['sucursal_id' => 1]);

        $cajaChicaBalance = $cajaChica->balance;
        $cajaGrandeBalance = $cajaGrande->balance;

        $hoy = Carbon::now('America/Guayaquil');
        $currentMonth = $hoy->month;
        $currentYear = $hoy->year;

        // Verificar si el mes actual está abierto
        $mensChica = CajaMensualidad::where('caja_id', $cajaChica->id)
            ->where('mes', $currentMonth)
            ->where('anio', $currentYear)
            ->first();
        $mensGrande = CajaMensualidad::where('caja_id', $cajaGrande->id)
            ->where('mes', $currentMonth)
            ->where('anio', $currentYear)
            ->first();

        $mesAbierto = $mensChica && $mensGrande;
        $mesCerrado = ($mensChica && $mensChica->cerrado) || ($mensGrande && $mensGrande->cerrado);

        // Cargar técnicos activos:
        // Si es Superadmin, todos los de Novitec.
        // Si es Admin UIO, los asignados a Novitec Quito (ID 1).
        $queryTecnicos = Usuario::where('activo', 1);
        if (!$esSuper) {
            $queryTecnicos->where(function ($q) {
                $q->where('sucursal_id', 1)
                  ->orWhereHas('sucursalesAsignadas', fn($s) => $s->where('sucursales.id', 1));
            });
        }
        $tecnicos = $queryTecnicos->get();

        // Filtro de movimientos
        $query = CajaMovimiento::query()->with(['caja', 'usuario', 'tecnico']);

        if ($request->filled('caja_tipo')) {
            $query->whereHas('caja', fn($q) => $q->where('tipo', $request->input('caja_tipo')));
        }
        if ($request->filled('mov_tipo')) {
            $query->where('tipo', $request->input('mov_tipo'));
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(50);

        return view('operations.caja.movimientos', compact(
            'esSuper',
            'cajaChicaBalance',
            'cajaGrandeBalance',
            'mesAbierto',
            'mesCerrado',
            'movimientos',
            'tecnicos',
            'cajaChica',
            'cajaGrande'
        ));
    }

    public function registrarMovimiento(Request $request)
    {
        if (!$this->checkAccess()) {
            return back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:500',
            'tecnico_id' => 'nullable|exists:usuarios,id',
            'fecha' => 'required|date',
            'justificante_1' => 'nullable|file|mimes:pdf,png,jpg,jpeg,gif|max:5120',
            'justificante_2' => 'nullable|file|mimes:pdf,png,jpg,jpeg,gif|max:5120',
        ]);

        $caja = Caja::findOrFail($request->input('caja_id'));

        $fecha = Carbon::parse($request->input('fecha'));
        $mes = $fecha->month;
        $anio = $fecha->year;

        // Comprobación de mes abierto y no cerrado
        $mensualidad = CajaMensualidad::where('caja_id', $caja->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if (!$mensualidad) {
            return back()->with('error', "El mes de {$fecha->format('m/Y')} no está abierto para esta caja. Debe aperturarlo primero.");
        }
        if ($mensualidad->cerrado) {
            return back()->with('error', "El mes de {$fecha->format('m/Y')} ya está cerrado. No se pueden registrar movimientos.");
        }

        $tipo = $request->input('tipo');
        $monto = (float) $request->input('monto');

        // Validar saldo para egresos
        if ($tipo === 'egreso') {
            if ($caja->balance < $monto) {
                return back()->with('error', 'Saldo insuficiente en la caja para registrar este egreso.');
            }
            if (!$request->hasFile('justificante_1')) {
                return back()->with('error', 'El primer justificante es obligatorio para registrar egresos.');
            }
        }

        try {
            DB::beginTransaction();

            $justificante1Path = null;
            $justificante2Path = null;

            if ($request->hasFile('justificante_1')) {
                $justificante1Path = $request->file('justificante_1')->store('justificantes_caja', 'public');
            }
            if ($request->hasFile('justificante_2')) {
                $justificante2Path = $request->file('justificante_2')->store('justificantes_caja', 'public');
            }

            CajaMovimiento::create([
                'caja_id' => $caja->id,
                'tipo' => $tipo,
                'categoria' => 'individual',
                'monto' => $monto,
                'descripcion' => $request->input('descripcion'),
                'usuario_id' => session('tecnico_id') ?? auth()->id(),
                'tecnico_id' => $request->input('tecnico_id'),
                'fecha' => $fecha->toDateString(),
                'justificante_1' => $justificante1Path,
                'justificante_2' => $justificante2Path,
            ]);

            // Actualizar balance de la caja
            if ($tipo === 'ingreso') {
                $caja->balance += $monto;
            } else {
                $caja->balance -= $monto;
            }
            $caja->save();

            DB::commit();
            return back()->with('success', 'Movimiento registrado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar movimiento de caja', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al registrar el movimiento.');
        }
    }

    public function editarMovimiento(Request $request, $id)
    {
        if (!$this->checkSuperadmin()) {
            return back()->with('error', 'Solo el superadministrador puede modificar movimientos.');
        }

        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:500',
            'tecnico_id' => 'nullable|exists:usuarios,id',
            'fecha' => 'required|date',
            'justificante_1' => 'nullable|file|mimes:pdf,png,jpg,jpeg,gif|max:5120',
            'justificante_2' => 'nullable|file|mimes:pdf,png,jpg,jpeg,gif|max:5120',
        ]);

        $mov = CajaMovimiento::findOrFail($id);
        $caja = $mov->caja;

        // Comprobación de mes cerrado
        $fechaNueva = Carbon::parse($request->input('fecha'));
        $mensNueva = CajaMensualidad::where('caja_id', $caja->id)
            ->where('mes', $fechaNueva->month)
            ->where('anio', $fechaNueva->year)
            ->first();

        if ($mensNueva && $mensNueva->cerrado) {
            return back()->with('error', 'El mes destino ya está cerrado.');
        }

        $montoViejo = (float) $mov->monto;
        $montoNuevo = (float) $request->input('monto');
        $tipo = $mov->tipo;

        try {
            DB::beginTransaction();

            // Calcular diferencia de saldo
            $diferencia = $montoNuevo - $montoViejo;
            if ($tipo === 'egreso') {
                // Si el egreso aumentó, validar saldo
                if ($diferencia > 0 && $caja->balance < $diferencia) {
                    throw new Exception('Saldo insuficiente en la caja.');
                }
                $caja->balance -= $diferencia;
            } else {
                $caja->balance += $diferencia;
            }

            if ($request->hasFile('justificante_1')) {
                $mov->justificante_1 = $request->file('justificante_1')->store('justificantes_caja', 'public');
            }
            if ($request->hasFile('justificante_2')) {
                $mov->justificante_2 = $request->file('justificante_2')->store('justificantes_caja', 'public');
            }

            $mov->monto = $montoNuevo;
            $mov->descripcion = $request->input('descripcion');
            $mov->tecnico_id = $request->input('tecnico_id');
            $mov->fecha = $fechaNueva->toDateString();
            $mov->save();

            $caja->save();

            DB::commit();
            return back()->with('success', 'Movimiento modificado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al modificar: ' . $e->getMessage());
        }
    }

    public function eliminarMovimiento($id)
    {
        if (!$this->checkSuperadmin()) {
            return back()->with('error', 'Solo el superadministrador puede eliminar movimientos.');
        }

        $mov = CajaMovimiento::findOrFail($id);
        $caja = $mov->caja;

        // Comprobación de mes cerrado
        $fecha = Carbon::parse($mov->fecha);
        $mensualidad = CajaMensualidad::where('caja_id', $caja->id)
            ->where('mes', $fecha->month)
            ->where('anio', $fecha->year)
            ->first();

        if ($mensualidad && $mensualidad->cerrado) {
            return back()->with('error', 'El mes correspondiente a este movimiento ya está cerrado.');
        }

        try {
            DB::beginTransaction();

            $monto = (float) $mov->monto;
            if ($mov->tipo === 'ingreso') {
                if ($caja->balance < $monto) {
                    throw new Exception('No se puede eliminar el ingreso porque el saldo restante quedaría negativo.');
                }
                $caja->balance -= $monto;
            } else {
                $caja->balance += $monto;
            }

            $mov->delete();
            $caja->save();

            DB::commit();
            return back()->with('success', 'Movimiento eliminado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function apertura(Request $request)
    {
        if (!$this->checkAccess()) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        $esSuper = $this->checkSuperadmin();

        $cajaChica = Caja::firstOrCreate(['tipo' => 'chica'], ['sucursal_id' => 1]);
        $cajaGrande = Caja::firstOrCreate(['tipo' => 'grande'], ['sucursal_id' => 1]);

        $mensualidades = CajaMensualidad::whereIn('caja_id', [$cajaChica->id, $cajaGrande->id])
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        return view('operations.caja.apertura', compact(
            'esSuper',
            'mensualidades'
        ));
    }

    public function abrirMes(Request $request)
    {
        if (!$this->checkAccess()) {
            return back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2026',
            'monto_ingreso_chica' => 'required|numeric|min:0',
            'monto_ingreso_grande' => 'required|numeric|min:0',
        ]);

        $mes = (int) $request->input('mes');
        $anio = (int) $request->input('anio');

        $cajaChica = Caja::firstOrCreate(['tipo' => 'chica'], ['sucursal_id' => 1]);
        $cajaGrande = Caja::firstOrCreate(['tipo' => 'grande'], ['sucursal_id' => 1]);

        // Validar si ya está abierto
        $existe = CajaMensualidad::where('caja_id', $cajaChica->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->exists();

        if ($existe) {
            return back()->with('error', 'El mes seleccionado ya se encuentra abierto.');
        }

        try {
            DB::beginTransaction();

            $tipos = [
                'chica' => [
                    'caja' => $cajaChica,
                    'ingreso' => (float) $request->input('monto_ingreso_chica')
                ],
                'grande' => [
                    'caja' => $cajaGrande,
                    'ingreso' => (float) $request->input('monto_ingreso_grande')
                ]
            ];

            foreach ($tipos as $tipo => $conf) {
                $caja = $conf['caja'];
                $montoIngreso = $conf['ingreso'];

                // Calcular saldo inicial basándose en el saldo final del mes anterior
                $prevMes = $mes === 1 ? 12 : $mes - 1;
                $prevAnio = $mes === 1 ? $anio - 1 : $anio;

                $prevMens = CajaMensualidad::where('caja_id', $caja->id)
                    ->where('mes', $prevMes)
                    ->where('anio', $prevAnio)
                    ->first();

                $saldoInicial = 0.00;
                if ($prevMens) {
                    $saldoInicial = $prevMens->cerrado 
                        ? $prevMens->saldo_cierre 
                        : $caja->balance;
                }

                // Crear mensualidad
                CajaMensualidad::create([
                    'caja_id' => $caja->id,
                    'mes' => $mes,
                    'anio' => $anio,
                    'saldo_inicial' => $saldoInicial,
                    'monto_ingreso' => $montoIngreso,
                    'cerrado' => false
                ]);

                // Registrar movimiento de recarga mensual
                CajaMovimiento::create([
                    'caja_id' => $caja->id,
                    'tipo' => 'ingreso',
                    'categoria' => 'mensualidad',
                    'monto' => $montoIngreso,
                    'descripcion' => "Apertura mensual - Recarga inicial {$mes}/{$anio}",
                    'usuario_id' => session('tecnico_id') ?? auth()->id(),
                    'fecha' => "{$anio}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01",
                ]);

                // Aumentar balance de la caja
                $caja->balance += $montoIngreso;
                $caja->save();
            }

            DB::commit();
            return back()->with('success', 'Mes aperturado correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al aperturar mes de caja', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al aperturar mes.');
        }
    }

    public function cerrarMes(Request $request)
    {
        if (!$this->checkAccess()) {
            return back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        $request->validate([
            'mensualidad_id' => 'required|exists:cajas_mensualidades,id'
        ]);

        $mens = CajaMensualidad::findOrFail($request->input('mensualidad_id'));
        $caja = $mens->caja;

        if ($mens->cerrado) {
            return back()->with('error', 'Este mes ya se encuentra cerrado.');
        }

        try {
            DB::beginTransaction();

            // Neto de movimientos excluyendo recargas
            $neto = CajaMovimiento::where('caja_id', $caja->id)
                ->whereMonth('fecha', $mens->mes)
                ->whereYear('fecha', $mens->anio)
                ->where('categoria', '!=', 'mensualidad')
                ->selectRaw("SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END) as neto")
                ->value('neto') ?? 0;

            $saldoCierre = $mens->saldo_inicial + $mens->monto_ingreso + $neto;

            $mens->saldo_cierre = $saldoCierre;
            $mens->cerrado = true;
            $mens->save();

            DB::commit();
            return back()->with('success', 'Mes cerrado correctamente. El balance se ha congelado.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cerrar mes.');
        }
    }

    public function reportes(Request $request)
    {
        if (!$this->checkAccess()) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        $esSuper = $this->checkSuperadmin();

        // Filtro por rangos de fecha
        $filtroPeriodo = $request->input('filtro_periodo', 'este_mes');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $hoy = Carbon::now('America/Guayaquil');

        switch ($filtroPeriodo) {
            case 'hoy':
                $fechaDesde = $hoy->toDateString();
                $fechaHasta = $hoy->toDateString();
                break;
            case 'esta_semana':
                $fechaDesde = $hoy->startOfWeek()->toDateString();
                $fechaHasta = $hoy->endOfWeek()->toDateString();
                break;
            case 'este_mes':
                $fechaDesde = $hoy->startOfMonth()->toDateString();
                $fechaHasta = $hoy->endOfMonth()->toDateString();
                break;
            case 'este_anio':
                $fechaDesde = $hoy->startOfYear()->toDateString();
                $fechaHasta = $hoy->endOfYear()->toDateString();
                break;
            case 'personalizado':
                break;
            default:
                $fechaDesde = $hoy->startOfMonth()->toDateString();
                $fechaHasta = $hoy->endOfMonth()->toDateString();
                break;
        }

        $cajaChica = Caja::firstOrCreate(['tipo' => 'chica'], ['sucursal_id' => 1]);
        $cajaGrande = Caja::firstOrCreate(['tipo' => 'grande'], ['sucursal_id' => 1]);

        $metricsChica = $this->calcularMetricas([$cajaChica->id], $fechaDesde, $fechaHasta);
        $metricsGrande = $this->calcularMetricas([$cajaGrande->id], $fechaDesde, $fechaHasta);

        // Comparador Mensual
        $comparativa = null;
        if ($request->filled('comp_mes_base') && $request->filled('comp_mes_ref')) {
            $baseParts = explode('-', $request->input('comp_mes_base'));
            $refParts = explode('-', $request->input('comp_mes_ref'));

            if (count($baseParts) === 2 && count($refParts) === 2) {
                $baseYear = (int) $baseParts[0];
                $baseMonth = (int) $baseParts[1];
                $refYear = (int) $refParts[0];
                $refMonth = (int) $refParts[1];

                $allIds = [$cajaChica->id, $cajaGrande->id];

                $dataBase = $this->calcularMetricasMensuales($allIds, $baseMonth, $baseYear);
                $dataRef = $this->calcularMetricasMensuales($allIds, $refMonth, $refYear);

                $comparativa = [
                    'base_nombre' => Carbon::createFromDate($baseYear, $baseMonth, 1)->format('F Y'),
                    'ref_nombre' => Carbon::createFromDate($refYear, $refMonth, 1)->format('F Y'),
                    'base' => $dataBase,
                    'ref' => $dataRef,
                    'ingresos_diff' => $dataBase['ingresos'] - $dataRef['ingresos'],
                    'egresos_diff' => $dataBase['egresos'] - $dataRef['egresos'],
                ];
            }
        }

        return view('operations.caja.reportes', compact(
            'esSuper',
            'filtroPeriodo',
            'fechaDesde',
            'fechaHasta',
            'metricsChica',
            'metricsGrande',
            'comparativa'
        ));
    }

    private function calcularMetricas(array $cajaIds, $desde, $hasta)
    {
        $ingresos = CajaMovimiento::whereIn('caja_id', $cajaIds)
            ->where('tipo', 'ingreso')
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('monto');

        $egresos = CajaMovimiento::whereIn('caja_id', $cajaIds)
            ->where('tipo', 'egreso')
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('monto');

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance' => $ingresos - $egresos
        ];
    }

    private function calcularMetricasMensuales(array $cajaIds, int $mes, int $anio)
    {
        $ingresos = CajaMovimiento::whereIn('caja_id', $cajaIds)
            ->where('tipo', 'ingreso')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('monto');

        $egresos = CajaMovimiento::whereIn('caja_id', $cajaIds)
            ->where('tipo', 'egreso')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('monto');

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance' => $ingresos - $egresos
        ];
    }
}
