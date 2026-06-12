import paramiko
import sys
import os

def deploy():
    hostname = "YOUR_SERVER_IP"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    # Local paths
    local_rule = r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\app\Rules\EcuadorIdentificacion.php"
    local_js = r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn\public\js\validador-ecuador.js"

    # Remote paths
    remote_rule = "/home/novitecadmin/novitec-stack/novitec-sgn/app/Rules/EcuadorIdentificacion.php"
    remote_js = "/home/novitecadmin/novitec-stack/novitec-sgn/public/js/validador-ecuador.js"

    try:
        sys.stdout.reconfigure(encoding='utf-8')
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(hostname, port=port, username=username, password=password, timeout=10)

        # Start SFTP client
        sftp = client.open_sftp()
        
        print(f"Uploading {local_rule} to {remote_rule}...")
        sftp.put(local_rule, remote_rule)
        print("Rule uploaded successfully!")

        print(f"Uploading {local_js} to {remote_js}...")
        sftp.put(local_js, remote_js)
        print("JS uploaded successfully!")
        
        sftp.close()

        # Rebuild docker images
        commands = [
            "docker compose -f /home/novitecadmin/novitec-stack/docker-compose.prod.yml build --no-cache novitec-sgn",
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
    deploy()
