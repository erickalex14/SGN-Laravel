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
        public array $fotos // Arreglo de archivos cargados (UploadedFile)
    ) {}
}