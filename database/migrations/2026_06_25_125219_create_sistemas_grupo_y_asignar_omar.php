<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Identity\GrupoAcceso;
use App\Models\Identity\Usuario;

return new class extends Migration
{
    public function up(): void
    {
        $grupo = GrupoAcceso::firstOrCreate(
            ['nombre' => 'Sistemas'],
            [
                'descripcion' => 'Grupo de Sistemas',
                'es_superadmin' => false
            ]
        );

        // Asignar a Omar Almeida (ID 8 o usuario 1755247887) a este grupo
        $usuario = Usuario::where('usuario', '1755247887')
            ->orWhere('id', 8)
            ->first();

        if ($usuario) {
            $usuario->grupo_id = $grupo->id;
            $usuario->save();
        }
    }

    public function down(): void
    {
        // No destructivo
    }
};
