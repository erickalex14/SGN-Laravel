import paramiko
import sys

def query():
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

        cmd = "ls -la /home/novitecadmin/novitec-stack/"
        stdin, stdout, stderr = client.exec_command(cmd)
        print("\n=== Stack Directory ===")
        print(stdout.read().decode('utf-8', errors='ignore'))

        cmd_compose = "cat /home/novitecadmin/novitec-stack/docker-compose.prod.yml || cat /home/novitecadmin/novitec-stack/docker-compose.yml"
        stdin, stdout, stderr = client.exec_command(cmd_compose)
        print("\n=== Docker Compose File ===")
        print(stdout.read().decode('utf-8', errors='ignore'))

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    query()
