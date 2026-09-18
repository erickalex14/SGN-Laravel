<?php

namespace App\Services\Operations;

use App\Models\Operations\Ticket;
use ZipArchive;

class TicketDocxService
{
    /**
     * Extrae los campos estructurados del Caso MBA3 desde la descripción del ticket.
     */
    public static function parsearDatosMba(Ticket $ticket): array
    {
        $desc = $ticket->descripcion ?? '';
        
        $icono = 'No especificado';
        $ruta = 'No especificado';
        $documentos = 'No especificado';
        $detalle = $desc;
        $accion = 'Revisión y resolución técnica por Sistemas';
        $usuarioReporta = $ticket->solicitante ? ($ticket->solicitante->nombre_tecnico ?: $ticket->solicitante->usuario) : 'Solicitante';
        $sucursal = $ticket->tienda_nombre ?: ($ticket->sucursalCliente ? $ticket->sucursalCliente->codigo . ' - ' . $ticket->sucursalCliente->nombre : 'Sucursal');
        $fecha = $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');

        if (preg_match('/(?:•|\-)?\s*Ícono de Acceso\s*:\s*([^\n\r]+)/ui', $desc, $m)) {
            $icono = trim($m[1]);
        }
        if (preg_match('/(?:•|\-)?\s*Ruta de Acceso\s*(?:\/\s*Módulo)?\s*:\s*([^\n\r]+)/ui', $desc, $m)) {
            $ruta = trim($m[1]);
        }
        if (preg_match('/(?:•|\-)?\s*Documentos\s*(?:\/\s*Datos Afectados)?\s*:\s*([^\n\r]+)/ui', $desc, $m)) {
            $documentos = trim($m[1]);
        }
        if (preg_match('/(?:📌|•|\-)?\s*DESCRIPCIÓN DETALLADA DEL PROBLEMA:\s*\n(.*?)(?:-{5,}|🎯|🎯\s*ACCIÓN|={5,}|$)/uis', $desc, $m)) {
            $detalle = trim($m[1]);
        }
        if (preg_match('/(?:🎯|•|\-)?\s*ACCIÓN REQUERIDA[^\n\r]*:\s*\n(.*?)(?:={5,}|$)/uis', $desc, $m)) {
            $accion = trim($m[1]);
        }

        return [
            'codigo' => $ticket->codigo_ticket,
            'titulo' => $ticket->titulo,
            'fecha' => $fecha,
            'sucursal' => $sucursal,
            'usuario_reporta' => $usuarioReporta,
            'empresa' => $ticket->empresa_origen ?: 'NOVICOMPU',
            'prioridad' => strtoupper($ticket->prioridad ?: 'MEDIA'),
            'icono' => $icono,
            'ruta' => $ruta,
            'documentos' => $documentos,
            'detalle' => $detalle,
            'accion' => $accion,
            'tecnico' => $ticket->asignadoA ? ($ticket->asignadoA->nombre_tecnico ?: $ticket->asignadoA->usuario) : 'Sin asignar',
            'estado' => strtoupper($ticket->estado ?: 'ABIERTO'),
            'solucion' => $ticket->solucion ?: ($ticket->solucion_texto ?: 'En proceso de atención'),
        ];
    }

    /**
     * Genera un archivo Word (.docx) formal con el formato oficial del Caso MBA3.
     */
    public function generarDocxCasoMba(Ticket $ticket): string
    {
        $d = self::parsearDatosMba($ticket);

        $tempFile = tempnam(sys_get_temp_dir(), 'mba_docx_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('No se pudo crear el archivo temporal .docx');
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
    <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>';

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

        // word/_rels/document.xml.rels
        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        // word/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:docDefaults>
        <w:rPrDefault>
            <w:rPr>
                <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
                <w:sz w:val="22"/>
                <w:szCs w:val="22"/>
                <w:color w:val="1E293B"/>
            </w:rPr>
        </w:rPrDefault>
    </w:docDefaults>
</w:styles>';

        // Helper para escapar XML
        $esc = fn($str) => htmlspecialchars((string)$str, ENT_XML1, 'UTF-8');

        // Construir párrafos para textos multilínea
        $formatearLineas = function($texto) use ($esc) {
            $lineas = explode("\n", str_replace("\r", "", $texto));
            $xml = '';
            foreach ($lineas as $idx => $linea) {
                if ($idx > 0) {
                    $xml .= '<w:p><w:pPr><w:spacing w:after="100"/></w:pPr><w:r><w:t>' . $esc($linea) . '</w:t></w:r></w:p>';
                } else {
                    $xml .= '<w:r><w:t>' . $esc($linea) . '</w:t></w:r>';
                }
            }
            return $xml;
        };

        // word/document.xml
        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>
        <!-- Encabezado Principal -->
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="60"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:b/>
                    <w:sz w:val="32"/>
                    <w:color w:val="1E40AF"/>
                </w:rPr>
                <w:t>SISTEMA DE GESTIÓN NOVITEC (SGN)</w:t>
            </w:r>
        </w:p>
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="160"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:b/>
                    <w:sz w:val="26"/>
                    <w:color w:val="0F172A"/>
                </w:rPr>
                <w:t>FORMULARIO OFICIAL DE REGISTRO - CASO MBA3</w:t>
            </w:r>
        </w:p>

        <!-- Banner Código y Estado -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="2563EB"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="2563EB"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="2563EB"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="2563EB"/>
                    <w:insideH w:val="none"/>
                    <w:insideV w:val="none"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:left w:w="160" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:right w:w="160" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="4600" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="EFF6FF"/>
                    </w:tcPr>
                    <w:p>
                        <w:r>
                            <w:rPr><w:b/><w:color w:val="1E40AF"/><w:sz w:val="24"/></w:rPr>
                            <w:t>TICKET: ' . $esc($d['codigo']) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="4600" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="EFF6FF"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:jc w:val="right"/></w:pPr>
                        <w:r>
                            <w:rPr><w:b/><w:color w:val="059669"/><w:sz w:val="22"/></w:rPr>
                            <w:t>ESTADO: ' . $esc($d['estado']) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <w:p><w:pPr><w:spacing w:after="160"/></w:pPr></w:p>

        <!-- SECCIÓN 1: DATOS GENERALES -->
        <w:p>
            <w:pPr><w:spacing w:after="80"/></w:pPr>
            <w:r>
                <w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="1E3A8A"/></w:rPr>
                <w:t>1. DATOS GENERALES DEL REQUERIMIENTO</w:t>
            </w:r>
        </w:p>

        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:left w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:bottom w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:right w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="100" w:type="dxa"/>
                    <w:left w:w="120" w:type="dxa"/>
                    <w:bottom w:w="100" w:type="dxa"/>
                    <w:right w:w="120" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Título / Asunto:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['titulo']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Tienda / Sucursal:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['sucursal']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Usuario Solicitante:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['usuario_reporta']) . ' (' . $esc($d['empresa']) . ')</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Fecha de Registro:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['fecha']) . ' · Prioridad: ' . $esc($d['prioridad']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <w:p><w:pPr><w:spacing w:after="160"/></w:pPr></w:p>

        <!-- SECCIÓN 2: PARÁMETROS TÉCNICOS MBA3 -->
        <w:p>
            <w:pPr><w:spacing w:after="80"/></w:pPr>
            <w:r>
                <w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="7C3AED"/></w:rPr>
                <w:t>2. PARÁMETROS TÉCNICOS MBA3</w:t>
            </w:r>
        </w:p>

        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:left w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:bottom w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:right w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="100" w:type="dxa"/>
                    <w:left w:w="120" w:type="dxa"/>
                    <w:bottom w:w="100" w:type="dxa"/>
                    <w:right w:w="120" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="FAF5FF"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Ícono de Acceso / URDP:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/><w:color w:val="7C3AED"/></w:rPr><w:t>' . $esc($d['icono']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="FAF5FF"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Ruta de Acceso / Módulo:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['ruta']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="FAF5FF"/></w:tcPr>
                    <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Documentos / Afectados:</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="6600" w:type="dxa"/></w:tcPr>
                    <w:p><w:r><w:t>' . $esc($d['documentos']) . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <w:p><w:pPr><w:spacing w:after="160"/></w:pPr></w:p>

        <!-- SECCIÓN 3: DESCRIPCIÓN DEL PROBLEMA -->
        <w:p>
            <w:pPr><w:spacing w:after="80"/></w:pPr>
            <w:r>
                <w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="B45309"/></w:rPr>
                <w:t>3. DESCRIPCIÓN DETALLADA DEL PROBLEMA / ANTECEDENTE</w:t>
            </w:r>
        </w:p>

        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="F59E0B"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="F59E0B"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="F59E0B"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="F59E0B"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="140" w:type="dxa"/>
                    <w:left w:w="160" w:type="dxa"/>
                    <w:bottom w:w="140" w:type="dxa"/>
                    <w:right w:w="160" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="9200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="FFFBEB"/></w:tcPr>
                    <w:p>
                        ' . $formatearLineas($d['detalle']) . '
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <w:p><w:pPr><w:spacing w:after="160"/></w:pPr></w:p>

        <!-- SECCIÓN 4: ACCIÓN REQUERIDA -->
        <w:p>
            <w:pPr><w:spacing w:after="80"/></w:pPr>
            <w:r>
                <w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="059669"/></w:rPr>
                <w:t>4. ACCIÓN REQUERIDA (RESOLUCIÓN ESPERADA)</w:t>
            </w:r>
        </w:p>

        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="10B981"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="10B981"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="10B981"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="10B981"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="140" w:type="dxa"/>
                    <w:left w:w="160" w:type="dxa"/>
                    <w:bottom w:w="140" w:type="dxa"/>
                    <w:right w:w="160" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="9200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="ECFDF5"/></w:tcPr>
                    <w:p>
                        ' . $formatearLineas($d['accion']) . '
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <w:p><w:pPr><w:spacing w:after="240"/></w:pPr></w:p>

        <!-- PIE DE PÁGINA / CONTROL -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>
                    <w:left w:val="none"/>
                    <w:bottom w:val="none"/>
                    <w:right w:val="none"/>
                    <w:insideH w:val="none"/>
                    <w:insideV w:val="none"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="100" w:type="dxa"/>
                    <w:left w:w="100" w:type="dxa"/>
                    <w:bottom w:w="100" w:type="dxa"/>
                    <w:right w:w="100" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="4600" w:type="dxa"/></w:tcPr>
                    <w:p>
                        <w:r>
                            <w:rPr><w:sz w:val="18"/><w:color w:val="64748B"/></w:rPr>
                            <w:t>Atendido por: ' . $esc($d['tecnico']) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="4600" w:type="dxa"/></w:tcPr>
                    <w:p>
                        <w:pPr><w:jc w:val="right"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="18"/><w:color w:val="64748B"/></w:rPr>
                            <w:t>Generado automáticamente desde Novitec SGN</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>
    </w:body>
</w:document>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);
        $zip->addFromString('word/styles.xml', $styles);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }
}
