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

        # Let's find equipment IDs with more than 1 entry in `equiposseries`
        cmd = 'docker exec novitec-sgn php artisan tinker --execute="\\$counts = \\\\App\\\\Models\\\\Operations\\\\EquipoSerie::groupBy(\'equipo_id\')->selectRaw(\'equipo_id, count(*) as cnt\')->having(\'cnt\', \'>\', 1)->get(); foreach (\\$counts as \\$c) { echo \'Equipo ID: \' . \\$c->equipo_id . \', Series count: \' . \\$c->cnt . PHP_EOL; }"'
        
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        if out:
            print("Multiple series equipos:")
            print(out)
        else:
            print("No equipment found with multiple series in the equiposseries table.")
        if err:
            print(f"Error/Stderr: {err}")

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    query()
