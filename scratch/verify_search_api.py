import paramiko
import sys

def verify():
    hostname = "181.198.104.181"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    try:
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        print(f"Connecting to {hostname}:{port}...")
        client.connect(hostname, port=port, username=username, password=password, timeout=10)
        print("SSH Connected.")

        cmd = 'docker exec novitec-sgn php artisan tinker --execute="\\$dto = new \\\\App\\\\DTOs\\\\Operations\\\\BuscarOrdenDTO(\'nro_orden\', \'UIO-000294\', 0, true, \'\', 0, \'\', \'\'); \\$repo = app(\\\\App\\\\Repositories\\\\Operations\\\\BuscarOrdenRepository::class); \\$res = \\$repo->buscar(\\$dto); if (\\$res->isNotEmpty()) { \\$o = \\$res->first(); echo \'Nro Orden: \' . \\$o->nro_orden . PHP_EOL; echo \'Facturas: \' . \\$o->nro_factura . \' / \' . \\$o->nro_factura_2 . PHP_EOL; echo \'Serie field returned by repo: \' . \\$o->serie . PHP_EOL; } else { echo \'Not found\'; }"'
        
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        if out:
            print(out)
        if err:
            print(f"Error/Stderr: {err}")

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    verify()
