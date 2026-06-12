import paramiko
import sys

def query():
    hostname = "YOUR_SERVER_IP"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        print(f"Connecting to {hostname}:{port}...")
        client.connect(hostname, port=port, username=username, password=password, timeout=10)
        print("SSH Connected.")

        cmd = 'docker exec novitec-sgn php artisan tinker --execute="\\$orden = \\\\App\\\\Models\\\\Operations\\\\Orden::whereNotNull(\'nro_factura_2\')->where(\'nro_factura_2\', \'<>\', \'\')->with([\'equipo.series\'])->latest(\'id\')->first(); if (\\$orden) { echo \'=== ORDEN ===\' . PHP_EOL; echo \'ID: \' . \\$orden->id . \', Nro: \' . \\$orden->nro_orden . PHP_EOL; echo \'Facturas: \' . \\$orden->nro_factura . \' / \' . \\$orden->nro_factura_2 . PHP_EOL; echo \'Equipo ID: \' . \\$orden->equipo_id . \', Serie base: \' . \\$orden->equipo->serie . PHP_EOL; echo \'Series relacion count: \' . \\$orden->equipo->series->count() . PHP_EOL; foreach (\\$orden->equipo->series as \\$s) { echo \' - Serie ID: \' . \\$s->id . \', Serie: \' . \\$s->serie . \', Orden: \' . \\$s->orden . PHP_EOL; } } else { echo \'No order found with nro_factura_2\'; }"'
        
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        if out:
            print(out)
        if err:
            print(f"Error/Stderr: {err}")

        # Let's also check the database schema or recent entries in `equiposseries`
        print("\n=== Recent entries in equiposseries ===")
        cmd2 = 'docker exec novitec-sgn php artisan tinker --execute="foreach (\\\\App\\\\Models\\\\Operations\\\\EquipoSerie::latest(\'id\')->take(5)->get() as \\$es) { echo \'ID: \' . \\$es->id . \', Equipo ID: \' . \\$es->equipo_id . \', Serie: \' . \\$es->serie . PHP_EOL; }"'
        stdin, stdout, stderr = client.exec_command(cmd2)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        if out:
            print(out)
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        if err:
            print(f"Error: {err}")

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    query()
