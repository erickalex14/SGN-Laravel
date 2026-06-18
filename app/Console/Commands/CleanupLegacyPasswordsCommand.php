<?php

namespace App\Console\Commands;

use App\Models\Identity\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanupLegacyPasswordsCommand extends Command
{
    protected $signature = 'auth:cleanup-legacy-passwords {--dry-run : Solo audita} {--execute : Aplica la limpieza}';

    protected $description = 'Audita y limpia usuarios.clave cuando ya existe un clave_hash valido';

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $stats = [
            'revisados' => 0,
            'con_hash' => 0,
            'con_plaintext' => 0,
            'limpiables' => 0,
            'limpiados' => 0,
            'revision' => 0,
        ];
        $samples = [];

        $this->components->info($execute ? 'Modo ejecucion' : 'Modo auditoria');

        Usuario::query()
            ->select(['id', 'usuario', 'clave', 'clave_hash'])
            ->orderBy('id')
            ->chunkById(100, function ($usuarios) use (&$stats, &$samples, $execute): void {
                foreach ($usuarios as $usuario) {
                    $stats['revisados']++;

                    $tieneHash = filled($usuario->clave_hash);
                    $tienePlaintext = filled($usuario->clave);

                    if ($tieneHash) {
                        $stats['con_hash']++;
                    }

                    if (! $tienePlaintext) {
                        continue;
                    }

                    $stats['con_plaintext']++;

                    if (! $tieneHash) {
                        $stats['revision']++;
                        $this->guardarMuestra($samples, $usuario, 'sin_hash');

                        continue;
                    }

                    if (! Hash::check((string) $usuario->clave, (string) $usuario->clave_hash)) {
                        $stats['revision']++;
                        $this->guardarMuestra($samples, $usuario, 'hash_no_coincide');

                        continue;
                    }

                    $stats['limpiables']++;

                    if (! $execute) {
                        continue;
                    }

                    DB::table('usuarios')
                        ->where('id', $usuario->id)
                        ->update(['clave' => '']);

                    $stats['limpiados']++;
                }
            });

        $this->line('Base actual: '.config('database.connections.'.config('database.default').'.database'));
        $this->table(
            ['Metricas', 'Valor'],
            [
                ['Usuarios revisados', $stats['revisados']],
                ['Usuarios con clave_hash', $stats['con_hash']],
                ['Usuarios con clave plaintext', $stats['con_plaintext']],
                ['Usuarios limpiables', $stats['limpiables']],
                ['Usuarios limpiados', $stats['limpiados']],
                ['Usuarios en revision', $stats['revision']],
            ]
        );

        if ($samples !== []) {
            $this->table(['ID', 'Usuario', 'Motivo'], $samples);
        }

        return self::SUCCESS;
    }

    private function guardarMuestra(array &$samples, Usuario $usuario, string $motivo): void
    {
        if (count($samples) >= 10) {
            return;
        }

        $samples[] = [
            'id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'motivo' => $motivo,
        ];
    }
}
