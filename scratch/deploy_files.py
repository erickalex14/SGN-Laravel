import paramiko
import os
import sys

def deploy():
    hostname = "YOUR_SERVER_IP"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    # Files to transfer: (local_path, remote_path)
    base_local = r"c:\Users\LENOVO\Desktop\WEB + SGN\novitec-sgn"
    base_remote = "/home/novitecadmin/novitec-stack/novitec-sgn"

    files_to_deploy = [
        (
            os.path.join(base_local, "resources", "views", "operations", "informes", "imprimir.blade.php"),
            base_remote + "/resources/views/operations/informes/imprimir.blade.php"
        ),
        (
            os.path.join(base_local, "resources", "views", "operations", "ordenes", "imprimir.blade.php"),
            base_remote + "/resources/views/operations/ordenes/imprimir.blade.php"
        ),
        (
            os.path.join(base_local, "resources", "views", "operations", "ordenes", "imprimir_empresa.blade.php"),
            base_remote + "/resources/views/operations/ordenes/imprimir_empresa.blade.php"
        ),
        (
            os.path.join(base_local, "app", "Repositories", "Operations", "BuscarOrdenRepository.php"),
            base_remote + "/app/Repositories/Operations/BuscarOrdenRepository.php"
        ),
        (
            os.path.join(base_local, "resources", "views", "operations", "ordenes", "buscar.blade.php"),
            base_remote + "/resources/views/operations/ordenes/buscar.blade.php"
        )
    ]

    try:
        # 1. Establish SSH connection
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        print(f"Connecting to {hostname}:{port} as {username}...")
        client.connect(hostname, port=port, username=username, password=password, timeout=15)
        print("SSH Connected successfully!\n")

        # 2. Establish SFTP connection
        sftp = client.open_sftp()
        print("SFTP Client opened.")

        for local, remote in files_to_deploy:
            print(f"Uploading:\n  Local:  {local}\n  Remote: {remote}")
            sftp.put(local, remote)
            print("Upload completed.")
            print("-" * 40)
        
        sftp.close()

        # 3. Rebuild docker container and clear cache
        commands = [
            "echo '=== Rebuilding novitec-sgn docker image ==='",
            "cd /home/novitecadmin/novitec-stack && docker compose -f docker-compose.prod.yml up -d --build novitec-sgn",
            "echo '=== Clearing Laravel Cache ==='",
            "docker exec novitec-sgn php artisan optimize:clear || echo 'Cache clear failed'"
        ]

        for cmd in commands:
            print(f"Executing remote command: {cmd}")
            stdin, stdout, stderr = client.exec_command(cmd)
            # Wait for command completion
            channel = stdout.channel
            # Read stdout line by line as it prints
            while not channel.exit_status_ready():
                if channel.recv_ready():
                    print(channel.recv(1024).decode('utf-8', errors='ignore'), end='')
            # Print final buffer
            print(stdout.read().decode('utf-8', errors='ignore'))
            err = stderr.read().decode('utf-8', errors='ignore').strip()
            if err:
                print(f"Stderr: {err}")
            print("-" * 40)

        client.close()
        print("Deployment and container rebuild finished successfully!")

    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    deploy()
