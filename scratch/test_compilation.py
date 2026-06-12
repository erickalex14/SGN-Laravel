import paramiko
import sys

def verify():
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

        # Let's test compiling each of the views by rendering them with dummy/first records
        # Using escaped \$ so bash doesn't expand them
        commands = [
            ("Test rendering 'operations.ordenes.imprimir' with the first Orden",
             'docker exec novitec-sgn php artisan tinker --execute="\\$orden = \\\\App\\\\Models\\\\Operations\\\\Orden::with([\'equipo.series\', \'tecnico\', \'sucursal\', \'cas\', \'usuarioIngreso\', \'repuestoInventario\', \'preciosOrden\', \'solicitudesNc\'])->first(); if (\\$orden) { echo \'Orden found: \' . \\$orden->id . PHP_EOL; echo \'Rendering view...\' . PHP_EOL; view(\'operations.ordenes.imprimir\', [\'orden\' => \\$orden, \'nombreSucursalCliente\' => \'TEST\'])->render(); echo \'RENDER OK\' . PHP_EOL; } else { echo \'No orders found to test.\'; }"'),
            
            ("Test rendering 'operations.informes.imprimir' with the first Informe",
             'docker exec novitec-sgn php artisan tinker --execute="\\$informe = \\\\App\\\\Models\\\\Operations\\\\Informe::with([\'orden.cliente\', \'orden.equipo\', \'tecnico\', \'fotos\'])->first(); if (\\$informe) { echo \'Informe found: \' . \\$informe->id . PHP_EOL; echo \'Rendering view...\' . PHP_EOL; view(\'operations.informes.imprimir\', [\'informe\' => \\$informe])->render(); echo \'RENDER OK\' . PHP_EOL; } else { echo \'No reports found to test.\'; }"'),
             
            ("Test rendering 'operations.ordenes.imprimir_empresa' with the first OrdenEmpresa",
             'docker exec novitec-sgn php artisan tinker --execute="\\$orden = \\\\App\\\\Models\\\\Operations\\\\OrdenEmpresa::with([\'empresa\', \'equipo\', \'tecnico\', \'sucursal\', \'ingresadoPor\'])->first(); if (\\$orden) { echo \'OrdenEmpresa found: \' . \\$orden->id . PHP_EOL; echo \'Rendering view...\' . PHP_EOL; view(\'operations.ordenes.imprimir_empresa\', [\'orden\' => \\$orden, \'nombreSucursalCliente\' => \'TEST\'])->render(); echo \'RENDER OK\' . PHP_EOL; } else { echo \'No corporate orders found to test.\'; }"')
        ]

        for desc, cmd in commands:
            print(f"\n{desc}:")
            stdin, stdout, stderr = client.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='ignore').strip()
            err = stderr.read().decode('utf-8', errors='ignore').strip()
            if out:
                print(out)
            if err:
                print(f"Error/Stderr: {err}")
            print("-" * 50)

        client.close()
        print("Verification complete.")

    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    verify()
