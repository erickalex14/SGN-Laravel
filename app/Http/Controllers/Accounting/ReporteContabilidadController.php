<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Directory\Sucursal;
use App\Models\Identity\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteContabilidadController extends Controller
{
    private function checkAccess($usuario)
    {
        $sa = session('es_superadmin');
        $p = session('permisos', []);
        $rolNombre = mb_strtolower(trim((string) ($usuario->rol->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario->grupo->nombre ?? '')));
        $esAdminMaster = $sa
            || (bool) ($usuario->grupo->es_superadmin ?? false)
            || in_array($rolNombre, ['admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin master', 'administrador master', 'superadministrador'], true);

        $tienePermiso = !empty($p['caja_general']['ver']) 
            || !empty($p['caja_chica']['ver']) 
            || !empty($p['recuento_b2b']['ver'])
            || !empty($p['reportes']['ver']);

        if (!$esAdminMaster && !$tienePermiso) {
            abort(403, 'Acceso denegado. No tienes permisos para acceder a Reportería y Auditoría de Contabilidad.');
        }

        return $esAdminMaster;
    }

    private function getCommonData(Request $request, $esAdminMaster, $usuario)
    {
        $fechaInicio = $request->query('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->query('fecha_fin', Carbon::now()->format('Y-m-d'));
        
        $sucursalesUserIds = session('sucursales_ids', []);
        if (empty($sucursalesUserIds) && !empty($usuario->sucursal_id)) {
            $sucursalesUserIds = [(int) $usuario->sucursal_id];
        }

        $sucursalFiltro = $request->query('sucursal_id', '');
        if (!$esAdminMaster && !empty($usuario->sucursal_id)) {
            $sucursalFiltro = (string) $usuario->sucursal_id;
        }

        $startDt = $fechaInicio . ' 00:00:00';
        $endDt = $fechaFin . ' 23:59:59';

        $sucursalesSelect = $esAdminMaster ? Sucursal::orderBy('ciudad')->get() : collect();
        $tecnicosSelect = Usuario::where('activo', 1)->orderBy('nombre_tecnico')->get(['id', 'nombre_tecnico', 'usuario']);

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursalesUserIds' => $sucursalesUserIds,
            'sucursalFiltro' => $sucursalFiltro,
            'startDt' => $startDt,
            'endDt' => $endDt,
            'sucursalesSelect' => $sucursalesSelect,
            'tecnicosSelect' => $tecnicosSelect
        ];
    }

    // 1. DASHBOARD DE KPIS & BALANCES CONSOLIDADOS (LÓGICA ANTI-DUPLICACIÓN BANCOS)
    public function kpis(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');
        $esAdminMaster = $this->checkAccess($usuario);
        $cd = $this->getCommonData($request, $esAdminMaster, $usuario);

        $qCobros = DB::table('caja_general_cobros')->whereBetween('fecha_cobro', [$cd['startDt'], $cd['endDt']]);
        if ($cd['sucursalFiltro'] !== '') $qCobros->where('sucursal_id', (int)$cd['sucursalFiltro']);
        $cobros = $qCobros->get();

        $cobrosEfectivoTodos = $cobros->where('destino_cuenta', 'Caja General');
        $cobrosBancosDirectos = $cobros->where('destino_cuenta', 'Bancos');

        // Efectivo Pendiente de Arqueo/Depósito (Dinero físico actual en ventanilla)
        $montoCobrosEfectivoPendiente = (float) $cobrosEfectivoTodos->filter(function($c) {
            return empty($c->arqueo_id) || ($c->estado_arqueo ?? 'Pendiente') === 'Pendiente';
        })->sum('monto_cobrado');

        // Efectivo Ya Arqueado y Depositado en Bancos
        $montoCobrosEfectivoDepositado = (float) $cobrosEfectivoTodos->filter(function($c) {
            return !empty($c->arqueo_id) && ($c->estado_arqueo ?? 'Pendiente') !== 'Pendiente';
        })->sum('monto_cobrado');

        $montoCobrosBancosDirectos = (float) $cobrosBancosDirectos->sum('monto_cobrado');

        // Caja Chica Gastos
        $qCajaChica = DB::table('caja_chica_detalle as d')
            ->join('caja_chica_cabecera as c', 'd.caja_chica_id', '=', 'c.id')
            ->whereBetween('d.created_at', [$cd['startDt'], $cd['endDt']]);
        if ($cd['sucursalFiltro'] !== '') $qCajaChica->where('c.sucursal_id', (int)$cd['sucursalFiltro']);
        $gastosCajaChicaTotal = (float) $qCajaChica->sum('d.total');

        // Recuento B2B
        $qB2B = DB::table('recuento_b2b_lote')->whereBetween('created_at', [$cd['startDt'], $cd['endDt']]);
        $lotesB2B = $qB2B->get();

        $totalConIvaB2B = (float) $lotesB2B->sum('total_con_iva');
        $netoBancoB2B = (float) $lotesB2B->sum('monto_neto_banco');
        $retRentaB2B = (float) $lotesB2B->sum('monto_retencion_renta');
        $retIvaB2B = (float) $lotesB2B->sum('monto_retencion_iva');

        // Balances Sin Duplicación:
        // 1. Balance Caja General (Solo efectivo pendiente de depósito)
        $balanceCajaGeneralPendiente = $montoCobrosEfectivoPendiente;
        // 2. Balance Caja Bancos (Transferencias directas + Depósitos Ventanilla + Acreditaciones B2B)
        $balanceCajaBancos = $montoCobrosBancosDirectos + $montoCobrosEfectivoDepositado + $netoBancoB2B;
        // 3. Balance Cajas Chicas
        $balanceCajaChica = $gastosCajaChicaTotal;
        // 4. Recaudación Total Ingresos Real (Efectivo Pendiente Ventanilla + Total Fondo Acreditado/Depositado en Bancos)
        $balanceNetoGlobal = $balanceCajaGeneralPendiente + $balanceCajaBancos;

        return view('accounting.reportes.kpis', array_merge($cd, [
            'esAdminMaster' => $esAdminMaster,
            'montoCobrosEfectivoPendiente' => $montoCobrosEfectivoPendiente,
            'montoCobrosEfectivoDepositado' => $montoCobrosEfectivoDepositado,
            'montoCobrosBancosDirectos' => $montoCobrosBancosDirectos,
            'totalGastosCajaChica' => $gastosCajaChicaTotal,
            'totalConIvaB2B' => $totalConIvaB2B,
            'netoBancoB2B' => $netoBancoB2B,
            'retRentaB2B' => $retRentaB2B,
            'retIvaB2B' => $retIvaB2B,
            'balanceCajaGeneralPendiente' => $balanceCajaGeneralPendiente,
            'balanceCajaBancos' => $balanceCajaBancos,
            'balanceCajaChica' => $balanceCajaChica,
            'balanceNetoGlobal' => $balanceNetoGlobal,
            'totalCobrosCant' => $cobros->count(),
            'totalB2BCant' => $lotesB2B->count()
        ]));
    }

    public function index(Request $request)
    {
        return $this->kpis($request);
    }

    // 2. CAJA GENERAL CON DETALLES DE ÓRDENES
    public function cajaGeneral(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');
        $esAdminMaster = $this->checkAccess($usuario);
        $cd = $this->getCommonData($request, $esAdminMaster, $usuario);

        $qCobros = DB::table('caja_general_cobros as c')
            ->leftJoin('vista_ordenes as v', 'c.nro_orden', '=', 'v.nro_orden')
            ->select(
                'c.*',
                'v.identificacion as cliente_cedula',
                'v.numero_contacto as cliente_telefono',
                'v.correo as cliente_correo',
                'v.tecnico as tecnico_orden',
                'v.tipo as equipo_tipo',
                'v.marca as equipo_marca',
                'v.modelo as equipo_modelo',
                'v.serie as equipo_serie',
                'v.falla as equipo_falla'
            )
            ->whereBetween('c.fecha_cobro', [$cd['startDt'], $cd['endDt']]);

        if ($cd['sucursalFiltro'] !== '') $qCobros->where('c.sucursal_id', (int)$cd['sucursalFiltro']);
        $cobros = $qCobros->orderByDesc('c.fecha_cobro')->get();

        $qArqueos = DB::table('caja_general_arqueo')->whereBetween('fecha', [$cd['startDt'], $cd['endDt']]);
        if ($cd['sucursalFiltro'] !== '') $qArqueos->where('sucursal_id', (int)$cd['sucursalFiltro']);
        $arqueos = $qArqueos->orderByDesc('fecha')->get();

        $totalEfectivoArqueado = (float) $arqueos->sum('monto_sistema');
        $totalFisicoArqueado = (float) $arqueos->sum('monto_fisico');
        $totalDiferenciaArqueos = (float) $arqueos->sum('diferencia');

        return view('accounting.reportes.caja_general', array_merge($cd, [
            'esAdminMaster' => $esAdminMaster,
            'cobros' => $cobros,
            'arqueos' => $arqueos,
            'totalEfectivoArqueado' => $totalEfectivoArqueado,
            'totalFisicoArqueado' => $totalFisicoArqueado,
            'totalDiferenciaArqueos' => $totalDiferenciaArqueos,
        ]));
    }

    // 3. CAJAS CHICAS POR SUCURSAL Y SELECCIONADA
    public function cajaChica(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');
        $esAdminMaster = $this->checkAccess($usuario);
        $cd = $this->getCommonData($request, $esAdminMaster, $usuario);

        $tipoGastoFiltro = $request->query('tipo_gasto', '');
        $tecnicoFiltro = $request->query('tecnico_id', '');
        $cajaChicaIdFiltro = $request->query('caja_chica_id', '');

        $qCabecerasCC = DB::table('caja_chica_cabecera as c')
            ->leftJoin('sucursales as s', 'c.sucursal_id', '=', 's.id')
            ->select('c.*', 's.ciudad as sucursal_ciudad');

        if ($cd['sucursalFiltro'] !== '') {
            $qCabecerasCC->where('c.sucursal_id', (int)$cd['sucursalFiltro']);
        }

        $cajasChicasCabeceras = $qCabecerasCC->orderByDesc('c.id')->get()->map(function($caja) use ($cd, $tipoGastoFiltro, $tecnicoFiltro) {
            $qDet = DB::table('caja_chica_detalle')
                ->where('caja_chica_id', $caja->id)
                ->whereBetween('created_at', [$cd['startDt'], $cd['endDt']]);

            if ($tipoGastoFiltro !== '') {
                $qDet->where('tipo_gasto', 'LIKE', '%' . $tipoGastoFiltro . '%');
            }
            if ($tecnicoFiltro !== '') {
                $qDet->where('usuario_beneficiado', 'LIKE', '%' . $tecnicoFiltro . '%');
            }

            $caja->total_gastos = (float) $qDet->sum('total');
            $caja->cant_gastos = $qDet->count();
            $caja->saldo_disponible = (float)$caja->fondo_inicial - $caja->total_gastos;
            return $caja;
        });

        $qCajaChica = DB::table('caja_chica_detalle as d')
            ->join('caja_chica_cabecera as c', 'd.caja_chica_id', '=', 'c.id')
            ->leftJoin('sucursales as s', 'c.sucursal_id', '=', 's.id')
            ->select('d.*', 'c.nro_caja_chica', 'c.codigo_sucursal', 'c.sucursal_id', 'c.custodio_nombre', 's.ciudad as sucursal_ciudad')
            ->whereBetween('d.created_at', [$cd['startDt'], $cd['endDt']]);

        if ($cd['sucursalFiltro'] !== '') {
            $qCajaChica->where('c.sucursal_id', (int)$cd['sucursalFiltro']);
        }
        if ($cajaChicaIdFiltro !== '') {
            $qCajaChica->where('d.caja_chica_id', (int)$cajaChicaIdFiltro);
        }
        if ($tipoGastoFiltro !== '') {
            $qCajaChica->where('d.tipo_gasto', 'LIKE', '%' . $tipoGastoFiltro . '%');
        }
        if ($tecnicoFiltro !== '') {
            $qCajaChica->where(function($q) use ($tecnicoFiltro) {
                $q->where('d.usuario_beneficiado', 'LIKE', '%' . $tecnicoFiltro . '%')
                  ->orWhere('c.custodio_nombre', 'LIKE', '%' . $tecnicoFiltro . '%');
            });
        }

        $gastosCajaChica = $qCajaChica->orderByDesc('d.created_at')->get();
        $totalGastosCajaChica = (float) $gastosCajaChica->sum('total');

        $tiposGastoSelect = DB::table('caja_chica_detalle')
            ->select('tipo_gasto')
            ->whereNotNull('tipo_gasto')
            ->where('tipo_gasto', '!=', '')
            ->distinct()
            ->pluck('tipo_gasto');

        return view('accounting.reportes.caja_chica', array_merge($cd, [
            'esAdminMaster' => $esAdminMaster,
            'cajaChicaIdFiltro' => $cajaChicaIdFiltro,
            'tipoGastoFiltro' => $tipoGastoFiltro,
            'tecnicoFiltro' => $tecnicoFiltro,
            'tiposGastoSelect' => $tiposGastoSelect,
            'cajasChicasCabeceras' => $cajasChicasCabeceras,
            'gastosCajaChica' => $gastosCajaChica,
            'totalGastosCajaChica' => $totalGastosCajaChica,
        ]));
    }

    // 4. RECUENTO B2B & BANCOS
    public function b2b(Request $request)
    {
        $usuario = auth()->user();
        if (!$usuario) return redirect()->route('login');
        $esAdminMaster = $this->checkAccess($usuario);
        $cd = $this->getCommonData($request, $esAdminMaster, $usuario);

        $qB2B = DB::table('recuento_b2b_lote')->whereBetween('created_at', [$cd['startDt'], $cd['endDt']]);
        $lotesB2B = $qB2B->orderByDesc('created_at')->get()->map(function($lote) {
            $lote->items = DB::table('recuento_b2b_item as i')
                ->leftJoin('vista_ordenes as v', 'i.nro_orden', '=', 'v.nro_orden')
                ->select(
                    'i.*',
                    'v.cliente as cliente_final_nombre',
                    'v.identificacion as cliente_identificacion',
                    'v.numero_contacto as cliente_telefono',
                    'v.correo as cliente_correo',
                    'v.tipo as equipo_tipo',
                    'v.marca as equipo_marca',
                    'v.modelo as equipo_modelo',
                    'v.serie as equipo_serie',
                    'v.falla as equipo_falla'
                )
                ->where('i.lote_id', $lote->id)
                ->get();
            return $lote;
        });

        $subtotalB2B = (float) $lotesB2B->sum('subtotal');
        $montoIvaB2B = (float) $lotesB2B->sum('monto_iva');
        $totalConIvaB2B = (float) $lotesB2B->sum('total_con_iva');
        $netoBancoB2B = (float) $lotesB2B->sum('monto_neto_banco');
        $retRentaB2B = (float) $lotesB2B->sum('monto_retencion_renta');
        $retIvaB2B = (float) $lotesB2B->sum('monto_retencion_iva');

        return view('accounting.reportes.b2b', array_merge($cd, [
            'esAdminMaster' => $esAdminMaster,
            'lotesB2B' => $lotesB2B,
            'subtotalB2B' => $subtotalB2B,
            'montoIvaB2B' => $montoIvaB2B,
            'totalConIvaB2B' => $totalConIvaB2B,
            'netoBancoB2B' => $netoBancoB2B,
            'retRentaB2B' => $retRentaB2B,
            'retIvaB2B' => $retIvaB2B,
        ]));
    }
}
