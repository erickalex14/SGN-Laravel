import paramiko
import sys

def deploy_edit_fields():
    hostname = "YOUR_SERVER_IP"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    # Files mapping: (local_path, remote_path)
    files = [
        (
            r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\app\DTOs\Operations\ActualizarOrdenDTO.php",
            "/home/novitecadmin/novitec-stack/novitec-sgn/app/DTOs/Operations/ActualizarOrdenDTO.php"
        ),
        (
            r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\app\Http\Requests\Operations\GuardarEdicionOrdenRequest.php",
            "/home/novitecadmin/novitec-stack/novitec-sgn/app/Http/Requests/Operations/GuardarEdicionOrdenRequest.php"
        ),
        (
            r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\app\Http\Controllers\Operations\EdicionOrdenController.php",
            "/home/novitecadmin/novitec-stack/novitec-sgn/app/Http/Controllers/Operations/EdicionOrdenController.php"
        ),
        (
            r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\app\Services\Operations\ActualizarOrdenService.php",
            "/home/novitecadmin/novitec-stack/novitec-sgn/app/Services/Operations/ActualizarOrdenService.php"
        ),
        (
            r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\resources\views\operations\ordenes\editar.blade.php",
            "/home/novitecadmin/novitec-stack/novitec-sgn/resources/views/operations/ordenes/editar.blade.php"
        )
    ]

    try:
        sys.stdout.reconfigure(encoding='utf-8')
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(hostname, port=port, username=username, password=password, timeout=10)

        # Start SFTP client
        sftp = client.open_sftp()
        for local_file, remote_file in files:
            print(f"Uploading {local_file} to {remote_file}...")
            sftp.put(local_file, remote_file)
            print("Uploaded successfully!")
        sftp.close()

        # Build and recreate using docker-compose (using cache)
        commands = [
            "docker compose -f /home/novitecadmin/novitec-stack/docker-compose.prod.yml build novitec-sgn",
            "docker compose -f /home/novitecadmin/novitec-stack/docker-compose.prod.yml up -d novitec-sgn",
            "docker exec novitec-sgn php artisan optimize:clear"
        ]

        for cmd in commands:
            print(f"Executing: {cmd}")
            stdin, stdout, stderr = client.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if out:
                print(f"Output:\n{out}")
            if err:
                print(f"Error/Stderr:\n{err}")
            print("-" * 40)

        client.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    deploy_edit_fields();
