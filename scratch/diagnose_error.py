import paramiko
import sys

def diagnose():
    hostname = "181.198.104.181"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    commands = [
        "docker ps -a",
        "docker logs novitec-sgn --tail 50",
        "docker logs novitec-web --tail 50",
        "netstat -tuln | grep 27639 || ss -tuln | grep 27639 || echo 'port 27639 not listening'"
    ]

    try:
        sys.stdout.reconfigure(encoding='utf-8')
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(hostname, port=port, username=username, password=password, timeout=10)

        for cmd in commands:
            print(f"Executing: {cmd}")
            stdin, stdout, stderr = client.exec_command(cmd)
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            if out:
                print(out)
            if err:
                print(f"Error: {err}")
            print("-" * 40)

        client.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    diagnose()
