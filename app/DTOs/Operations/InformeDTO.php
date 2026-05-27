<?php

namespace App\DTOs\Operations;

readonly class InformeDTO
{
    public function __construct(
        public int $orden_id,
        public int $tecnico_id,
        public string $antecedentes,
        public string $proceso,
        public string $conclusion,
        public ?string $recomendaciones,
        public string $estado_equipo,
        public ?string $fecha_informe,
        public array $fotos, // Arreglo de archivos cargados (UploadedFile)
        public array $captions = []
    ) {}
}
