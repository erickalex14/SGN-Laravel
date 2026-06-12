import paramiko
import sys

def query():
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

        cmd = 'docker exec novitec-sgn php artisan tinker --execute="\\$informe = \\\\App\\\\Models\\\\Operations\\\\Informe::where(\'orden_id\', 950)->first(); if (\\$informe) { echo \'Informe ID: \' . \\$informe->id . \', Orden ID: \' . \\$informe->orden_id . PHP_EOL; } else { echo \'No informe found for orden_id 950\' . PHP_EOL; }"'
        
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
    query()
