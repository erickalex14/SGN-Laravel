import paramiko
import sys
import os

def deploy_clock():
    hostname = "181.198.104.181"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    # Local path
    local_layout = r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\resources\views\layouts\app.blade.php"

    # Remote path
    remote_layout = "/home/novitecadmin/novitec-stack/novitec-sgn/resources/views/layouts/app.blade.php"

    try:
        sys.stdout.reconfigure(encoding='utf-8')
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(hostname, port=port, username=username, password=password, timeout=10)

        # Start SFTP client
        sftp = client.open_sftp()
        print(f"Uploading {local_layout} to {remote_layout}...")
        sftp.put(local_layout, remote_layout)
        print("Layout file uploaded successfully!")
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
    deploy_clock()
