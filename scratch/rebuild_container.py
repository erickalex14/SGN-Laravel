import paramiko
import sys
import io

def run():
    # Configure stdout to handle UTF-8/Unicode characters safely
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')

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

        commands = [
            "echo '=== Docker Build and Up ==='",
            "cd /home/novitecadmin/novitec-stack && docker compose -f docker-compose.prod.yml up -d --build novitec-sgn",
            "echo '=== Clear Cache ==='",
            "docker exec novitec-sgn php artisan optimize:clear"
        ]

        for cmd in commands:
            print(f"\nExecuting: {cmd}")
            stdin, stdout, stderr = client.exec_command(cmd)
            
            # Wait for execution and stream stdout
            channel = stdout.channel
            while not channel.exit_status_ready():
                if channel.recv_ready():
                    text = channel.recv(4096).decode('utf-8', errors='ignore')
                    # Print safely by encoding to terminal's stdout encoding with replacement
                    sys.stdout.write(text.encode(sys.stdout.encoding, errors='replace').decode(sys.stdout.encoding))
                    sys.stdout.flush()

            # Read any remaining output
            out = stdout.read().decode('utf-8', errors='ignore')
            if out:
                sys.stdout.write(out.encode(sys.stdout.encoding, errors='replace').decode(sys.stdout.encoding))
                sys.stdout.flush()

            err = stderr.read().decode('utf-8', errors='ignore').strip()
            if err:
                print(f"\nStderr/Error: {err}")
            print("-" * 50)

        client.close()
        print("Rebuild and restart completed successfully!")

    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    run()
