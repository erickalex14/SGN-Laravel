import paramiko
import sys
import re

def render():
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

        cmd = 'docker exec novitec-sgn php artisan tinker --execute="echo view(\'operations.ordenes.imprimir\', [\'orden\' => \\\\App\\\\Models\\\\Operations\\\\Orden::find(950), \'nombreSucursalCliente\' => \'TEST\'])->render();"'
        
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        
        if out:
            print("HTML Rendered. Searching for series and invoice fields...")
            # Let's search for "Serie" and the serial numbers
            series_matches = re.findall(r'<td colspan="4">.*?</td>', out, re.DOTALL)
            print("\nFound series section in HTML:")
            for m in series_matches:
                print(m.strip())

            invoice_matches = re.findall(r'<td>.*?Nro\. Factura.*?</td>', out, re.DOTALL)
            print("\nFound invoice section in HTML:")
            for m in invoice_matches:
                print(m.strip())

        if err:
            print(f"Error/Stderr: {err}")

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    render()
