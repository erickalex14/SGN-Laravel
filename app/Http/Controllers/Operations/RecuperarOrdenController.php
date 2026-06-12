<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Directory\Cliente;
use App\Models\Directory\Sucursal;
use App\Models\Identity\Usuario;
use App\Models\Operations\Equipo;
use App\Models\Operations\Orden;
use App\Models\Operations\Informe;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Exception;

class RecuperarOrdenController extends Controller
{
    /**
     * Muestra la interfaz del Asistente de Recuperación.
     */
    public function index(Request $request): View
    {
        $this->verificarPermiso();

        $type = $request->query('type', 'orden'); // default to 'orden'
        $sucursales = Sucursal::orderBy('ciudad')->get();
        $tecnicos = Usuario::where('activo', 1)->orderBy('nombre_tecnico')->get();

        return view('operations.ordenes.recuperar', compact('sucursales', 'tecnicos', 'type'));
    }

    /**
     * Sube y analiza el archivo PDF para extraer la información.
     */
    public function analizar(Request $request): JsonResponse
    {
        try {
            $this->verificarPermiso();

            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240',
            ]);

            $file = $request->file('pdf_file');
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();

            $isOcrEmpty = empty(trim($text));
            $usedOcr = false;

            if ($isOcrEmpty) {
                $ocrText = $this->runOcrOnPdf($file->getPathname());
                if (!empty(trim($ocrText))) {
                    $text = $ocrText;
                    $isOcrEmpty = false;
                    $usedOcr = true;
                }
            }

            $lines = [];
            if (!$isOcrEmpty) {
                // Procesar el texto extraído
                $lines = array_map('trim', explode("\n", $text));
                $lines = array_values(array_filter($lines, fn($l) => $l !== ''));
            }

            $isInforme = false;
            foreach ($lines as $line) {
                if (stripos($line, 'INFORME TÉCNICO') !== false 
                    || stripos($line, 'INFORME TECNICO') !== false
                    || stripos($line, 'INFORME') !== false
                    || stripos($line, 'ANTECEDENTES') !== false
                    || stripos($line, 'PROCESO TÉCNICO') !== false
                    || stripos($line, 'PROCESO TECNICO') !== false
                    || stripos($line, 'CONCLUSIÓN') !== false
                    || stripos($line, 'CONCLUSION') !== false
                    || stripos($line, 'RECOMENDACIONES') !== false
                ) {
                    $isInforme = true;
                    break;
                }
            }

            // 1. Extraer Nro de Orden
            $nroOrden = '';
            foreach ($lines as $line) {
                if (preg_match('/([A-Z]{3}-\d+)/', $line, $matches)) {
                    $nroOrden = $matches[1];
                    break;
                }
            }

            // 2. Extraer Identificación
            $identificacion = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'C.I / RUC') !== false || stripos($line, 'Identificación / RUC') !== false || stripos($line, 'Identificaci') !== false) {
                    if (isset($lines[$i + 1])) {
                        $identificacion = $lines[$i + 1];
                        break;
                    }
                }
            }

            // 3. Extraer Nombre del Cliente
            $nombres = '';
            $apellidos = '';
            $clienteLine = '';
            foreach ($lines as $i => $line) {
                if (strcasecmp($line, 'Cliente') === 0 || (stripos($line, 'Cliente') !== false && stripos($line, 'Datos del') === false)) {
                    if (isset($lines[$i + 1])) {
                        $clienteLine = $lines[$i + 1];
                        break;
                    }
                }
            }

            if (!empty($clienteLine)) {
                $parts = array_filter(explode(' ', $clienteLine));
                $parts = array_values($parts);
                if (count($parts) >= 4) {
                    $nombres = $parts[0] . ' ' . $parts[1];
                    $apellidos = implode(' ', array_slice($parts, 2));
                } elseif (count($parts) == 3) {
                    $nombres = $parts[0];
                    $apellidos = $parts[1] . ' ' . $parts[2];
                } elseif (count($parts) == 2) {
                    $nombres = $parts[0];
                    $apellidos = $parts[1];
                } else {
                    $nombres = $clienteLine;
                    $apellidos = '';
                }
            }

            // 4. Teléfono
            $telefono = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Telefono') !== false || stripos($line, 'Teléfono') !== false) {
                    if (stripos($line, 'Teléfonos') !== false) continue;
                    if (isset($lines[$i + 1])) {
                        $telefono = $lines[$i + 1];
                        break;
                    }
                }
            }

            // 5. Correo
            $correo = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Correo') !== false || stripos($line, 'Email') !== false) {
                    if (isset($lines[$i + 1]) && strpos($lines[$i + 1], '@') !== false) {
                        $correo = $lines[$i + 1];
                        break;
                    }
                }
            }

            // 6. Dirección
            $direccion = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Direccion') !== false || stripos($line, 'Dirección') !== false) {
                    if (isset($lines[$i + 1])) {
                        $direccion = $lines[$i + 1];
                        break;
                    }
                }
            }

            // 7. Factura / Ticket
            $nroFactura = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Nro. Factura') !== false || stripos($line, 'Nro. Ticket') !== false) {
                    if (isset($lines[$i + 1]) && $lines[$i + 1] !== '-') {
                        $nroFactura = $lines[$i + 1];
                        break;
                    }
                }
            }

            // 8. Datos del Equipo
            $equipoTipo = '';
            $equipoMarca = '';
            $equipoModelo = '';
            $equipoSerie = '';

            foreach ($lines as $i => $line) {
                if (strcasecmp($line, 'Tipo') === 0) {
                    if (isset($lines[$i + 1])) { $equipoTipo = $lines[$i + 1]; break; }
                }
            }
            foreach ($lines as $i => $line) {
                if (strcasecmp($line, 'Marca') === 0) {
                    if (isset($lines[$i + 1])) { $equipoMarca = $lines[$i + 1]; break; }
                }
            }
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Codigo / Modelo') !== false || stripos($line, 'Código / Modelo') !== false || strcasecmp($line, 'Modelo') === 0) {
                    if (isset($lines[$i + 1])) { $equipoModelo = $lines[$i + 1]; break; }
                }
            }
            foreach ($lines as $i => $line) {
                if (strcasecmp($line, 'Serie') === 0 || stripos($line, 'Serie') !== false) {
                    if (isset($lines[$i + 1])) { $equipoSerie = $lines[$i + 1]; break; }
                }
            }

            // Falla & Observación
            $falla = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Falla Reportada') !== false || stripos($line, 'Falla') !== false) {
                    if (isset($lines[$i + 1])) { $falla = $lines[$i + 1]; break; }
                }
            }
            $observacion = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Observacion') !== false || stripos($line, 'Observación') !== false) {
                    if (isset($lines[$i + 1])) { $observacion = $lines[$i + 1]; break; }
                }
            }

            // Motivo Ingreso
            $motivoIngreso = 'Servicio Cliente Externo';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Motivo de Ingreso') !== false || stripos($line, 'Motivo') !== false) {
                    if (isset($lines[$i + 1])) { $motivoIngreso = $lines[$i + 1]; break; }
                }
            }

            // Técnico responsable en el PDF (para intentar emparejar)
            $tecnicoNombrePdf = '';
            foreach ($lines as $i => $line) {
                if (stripos($line, 'Tecnico Asignado') !== false || stripos($line, 'Técnico Asignado') !== false || strcasecmp($line, 'Técnico') === 0 || strcasecmp($line, 'Tecnico') === 0) {
                    if (isset($lines[$i + 1])) { $tecnicoNombrePdf = $lines[$i + 1]; break; }
                }
            }

            // Campos de informe si aplica
            $antecedentes = '';
            $proceso = '';
            $conclusion = '';
            $recomendaciones = '';
            $estadoEquipo = 'Operativo';

            // Intentamos extraer los campos del informe de forma incondicional
            $possibleNextKeys = ['Proceso', 'Conclusión', 'Conclusion', 'Recomendaciones', 'Evidencia Fotográfica', 'Firmas', 'Técnico responsable', 'Recibido conforme'];
            $antecedentes = $this->getSectionText($lines, 'Antecedentes', $possibleNextKeys);

            $possibleNextKeysProceso = ['Conclusión', 'Conclusion', 'Recomendaciones', 'Evidencia Fotográfica', 'Firmas', 'Técnico responsable', 'Recibido conforme'];
            $proceso = $this->getSectionText($lines, 'Proceso', $possibleNextKeysProceso);

            $possibleNextKeysConclusion = ['Recomendaciones', 'Evidencia Fotográfica', 'Firmas', 'Técnico responsable', 'Recibido conforme'];
            $conclusion = $this->getSectionText($lines, 'Conclusión', $possibleNextKeysConclusion);
            if (empty($conclusion)) {
                $conclusion = $this->getSectionText($lines, 'Conclusion', $possibleNextKeysConclusion);
            }

            $possibleNextKeysRecomendaciones = ['Evidencia Fotográfica', 'Firmas', 'Técnico responsable', 'Recibido conforme'];
            $recomendaciones = $this->getSectionText($lines, 'Recomendaciones', $possibleNextKeysRecomendaciones);

            foreach ($lines as $line) {
                foreach (['Operativo', 'Reparado parcialmente', 'Sin reparación posible', 'Desguace', 'En espera de repuesto'] as $est) {
                    if (strcasecmp(trim($line), $est) === 0) {
                        $estadoEquipo = $est;
                        break 2;
                    }
                }
            }

            // Si se extrajo contenido de alguna sección del informe, forzamos $isInforme = true
            if (!empty($antecedentes) || !empty($proceso) || !empty($conclusion) || !empty($recomendaciones)) {
                $isInforme = true;
            }

            // Verificar si la orden ya existe en el sistema
            $ordenExistente = false;
            $informeExistente = false;
            if (!empty($nroOrden)) {
                $ord = Orden::where('nro_orden', $nroOrden)->first();
                if ($ord) {
                    $ordenExistente = true;
                    if (Informe::where('orden_id', $ord->id)->exists()) {
                        $informeExistente = true;
                    }
                }
            }

            $warningMsg = null;
            if ($isOcrEmpty) {
                $warningMsg = 'No pudimos extraer el texto de este PDF de forma automática (es posible que el archivo esté vacío o no tenga texto). Por favor, introduce los datos manualmente.';
            } elseif ($usedOcr) {
                $warningMsg = 'El PDF es un escaneo sin texto y fue procesado con reconocimiento de caracteres (OCR) automático. Por favor, revisa detalladamente que todos los campos extraídos sean correctos.';
            }

            return response()->json([
                'ok' => true,
                'advertencia' => $warningMsg,
                'data' => [
                    'is_informe' => $isInforme,
                    'nro_orden' => $nroOrden,
                    'cliente_identificacion' => $identificacion,
                    'cliente_nombres' => $nombres,
                    'cliente_apellidos' => $apellidos,
                    'cliente_telefono' => $telefono,
                    'cliente_correo' => $correo,
                    'cliente_direccion' => $direccion,
                    'nro_factura' => $nroFactura,
                    'equipo_tipo' => $equipoTipo,
                    'equipo_marca' => $equipoMarca,
                    'equipo_modelo' => $equipoModelo,
                    'equipo_serie' => $equipoSerie,
                    'falla' => $falla,
                    'observacion' => $observacion,
                    'motivo_ingreso' => $motivoIngreso,
                    'tecnico_nombre_pdf' => $tecnicoNombrePdf,
                    'antecedentes' => $antecedentes,
                    'proceso' => $proceso,
                    'conclusion' => $conclusion,
                    'recomendaciones' => $recomendaciones,
                    'estado_equipo' => $estadoEquipo,
                    'orden_existente' => $ordenExistente,
                    'informe_existente' => $informeExistente,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Error al analizar el PDF: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesa y guarda los datos revisados por el administrador.
     */
    public function guardar(Request $request): JsonResponse
    {
        $this->verificarPermiso();

        $isInformeOnly = ($request->input('is_informe') == 1);

        if ($isInformeOnly) {
            $request->validate([
                'nro_orden' => 'required|string',
                'tecnico_id' => 'required|integer',
                'is_informe' => 'required|boolean',
            ]);
        } else {
            $request->validate([
                'nro_orden' => 'required|string',
                'cliente_identificacion' => 'required|string',
                'cliente_nombres' => 'required|string',
                'cliente_apellidos' => 'nullable|string',
                'equipo_tipo' => 'required|string',
                'equipo_marca' => 'required|string',
                'equipo_modelo' => 'required|string',
                'equipo_serie' => 'required|string',
                'sucursal_id' => 'required|integer',
                'tecnico_id' => 'required|integer',
                'is_informe' => 'required|boolean',
            ]);
        }

        DB::beginTransaction();
        try {
            $nroOrden = trim($request->input('nro_orden'));
            $orden = Orden::where('nro_orden', $nroOrden)->first();

            if ($isInformeOnly) {
                // Si es solo recuperación de informe, la orden debe existir previamente.
                if (!$orden) {
                    return response()->json([
                        'ok' => false,
                        'error' => "La orden '{$nroOrden}' no existe en el sistema. Para asociar un informe técnico, primero debes recuperar o registrar la orden de trabajo respectiva.",
                    ]);
                }

                // Actualizar el estado de la orden a 'Finalizada'
                $orden->estado_orden = 'Finalizada';
                $orden->save();
            } else {
                // 1. Guardar/Actualizar Cliente
                $cliente = Cliente::updateOrCreate(
                    ['identificacion' => $request->input('cliente_identificacion')],
                    [
                        'nombres' => $request->input('cliente_nombres'),
                        'apellidos' => $request->input('cliente_apellidos') ?? '',
                        'numero_contacto' => $request->input('cliente_telefono') ?? '',
                        'correo' => $request->input('cliente_correo') ?? '',
                        'direccion_clientes' => $request->input('cliente_direccion') ?? '',
                    ]
                );

                // 2. Guardar/Actualizar Equipo
                $equipo = Equipo::create([
                    'tipo' => $request->input('equipo_tipo'),
                    'marca' => $request->input('equipo_marca'),
                    'modelo' => $request->input('equipo_modelo'),
                    'serie' => $request->input('equipo_serie'),
                    'falla' => $request->input('falla') ?? '',
                    'observacion' => $request->input('observacion') ?? '',
                ]);

                if (!$orden) {
                    $orden = Orden::create([
                        'nro_orden' => $nroOrden,
                        'cliente_id' => $cliente->id,
                        'equipo_id' => $equipo->id,
                        'sucursal_id' => $request->input('sucursal_id'),
                        'tecnico_id' => $request->input('tecnico_id'),
                        'nro_factura' => $request->input('nro_factura') ?? '',
                        'motivo_ingreso' => $request->input('motivo_ingreso') ?? 'Servicio Cliente Externo',
                        'estado_orden' => 'Pendiente',
                        'estado_repuesto' => 'No requerido',
                        'fecha_de_ingreso' => now(),
                        'ingresado_por' => auth()->id(),
                        'observacion' => $request->input('observacion') ?? '',
                    ]);
                }
            }

            // 4. Guardar Informe si es necesario
            if ($isInformeOnly) {
                // Verificar si ya existe informe para esta orden
                $informe = Informe::where('orden_id', $orden->id)->first();
                if (!$informe) {
                    Informe::create([
                        'orden_id' => $orden->id,
                        'tecnico_id' => $request->input('tecnico_id'),
                        'antecedentes' => $request->input('antecedentes') ?? '',
                        'proceso' => $request->input('proceso') ?? '',
                        'conclusion' => $request->input('conclusion') ?? '',
                        'recomendaciones' => $request->input('recomendaciones') ?? '',
                        'estado_equipo' => $request->input('estado_equipo') ?? 'Operativo',
                        'fecha_informe' => now(),
                        'fecha_creacion' => now(),
                    ]);
                } else {
                    // Actualizar el existente
                    $informe->antecedentes = $request->input('antecedentes') ?? '';
                    $informe->proceso = $request->input('proceso') ?? '';
                    $informe->conclusion = $request->input('conclusion') ?? '';
                    $informe->recomendaciones = $request->input('recomendaciones') ?? '';
                    $informe->estado_equipo = $request->input('estado_equipo') ?? 'Operativo';
                    $informe->save();
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'mensaje' => $isInformeOnly 
                    ? 'Informe técnico reconstruido y asociado con éxito.'
                    : 'Orden de trabajo reconstruida con éxito.',
                'orden_id' => $orden->id,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false,
                'error' => 'Error al guardar los datos: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Helper para extraer textos de secciones.
     */
    private function getSectionText(array $lines, string $sectionName, array $nextPossibleKeys): string
    {
        $startIndex = -1;
        foreach ($lines as $i => $line) {
            if (strcasecmp($line, $sectionName) === 0) {
                $startIndex = $i;
                break;
            }
        }
        if ($startIndex === -1) return '';

        $endIndex = count($lines);
        for ($j = $startIndex + 1; $j < count($lines); $j++) {
            foreach ($nextPossibleKeys as $key) {
                if (strcasecmp($lines[$j], $key) === 0 || stripos($lines[$j], $key) !== false) {
                    $endIndex = $j;
                    break 2;
                }
            }
        }

        $contentLines = [];
        for ($k = $startIndex + 1; $k < $endIndex; $k++) {
            $contentLines[] = $lines[$k];
        }
        return trim(implode("\n", $contentLines));
    }

    /**
     * Valida que el usuario tenga privilegios de administrador.
     */
    private function verificarPermiso()
    {
        $usuario = auth()->user();
        $rolNombre = mb_strtolower(trim((string) ($usuario?->rol?->rol ?? '')));
        $grupoNombre = mb_strtolower(trim((string) ($usuario?->grupo?->nombre ?? '')));
        $sessionGrupo = mb_strtolower(trim((string) session('grupo_nombre', '')));

        $esAdminOAdminMaster = in_array($rolNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($grupoNombre, ['admin', 'administrador', 'admin master', 'administrador master'], true)
            || in_array($sessionGrupo, ['admin', 'administrador', 'admin master', 'administrador master'], true);

        abort_if(!$esAdminOAdminMaster, 403, 'No tienes permisos para acceder a esta sección.');
    }

    /**
     * Ejecuta OCR en un PDF convirtiéndolo a imágenes y pasando tesseract.
     */
    private function runOcrOnPdf(string $pdfPath): string
    {
        $tempDir = storage_path('app/temp_ocr_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $pdfPathEscaped = escapeshellarg($pdfPath);
        $outputPrefix = escapeshellarg($tempDir . '/page');

        // Convertir PDF a imágenes PNG a 150 DPI
        $cmdPpm = "pdftoppm -png -r 150 {$pdfPathEscaped} {$outputPrefix} 2>&1";
        exec($cmdPpm, $ppmOutput, $ppmStatus);

        if ($ppmStatus !== 0) {
            $this->cleanDir($tempDir);
            return '';
        }

        // Obtener todas las imágenes generadas ordenadas
        $files = glob($tempDir . '/page-*.png');
        natsort($files);

        $extractedText = '';

        foreach ($files as $imagePath) {
            $imagePathEscaped = escapeshellarg($imagePath);
            $textOutputPrefix = $tempDir . '/' . basename($imagePath, '.png') . '-txt';
            $textOutputPrefixEscaped = escapeshellarg($textOutputPrefix);

            // Ejecutar Tesseract OCR en español
            $cmdTesseract = "tesseract {$imagePathEscaped} {$textOutputPrefixEscaped} -l spa 2>&1";
            exec($cmdTesseract, $tessOutput, $tessStatus);

            $txtFile = $textOutputPrefix . '.txt';
            if (file_exists($txtFile)) {
                $extractedText .= file_get_contents($txtFile) . "\n";
            }
        }

        // Limpiar directorio temporal
        $this->cleanDir($tempDir);

        return $extractedText;
    }

    /**
     * Limpia un directorio temporal y lo elimina.
     */
    private function cleanDir(string $dir)
    {
        if (!file_exists($dir)) return;
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
