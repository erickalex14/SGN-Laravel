import paramiko
import sys
import json

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

        cmd = "docker inspect novitec-sgn"
        stdin, stdout, stderr = client.exec_command(cmd)
        out = stdout.read().decode('utf-8', errors='ignore').strip()
        err = stderr.read().decode('utf-8', errors='ignore').strip()
        
        if out:
            data = json.loads(out)
            if data:
                container = data[0]
                print("\n=== Container Name ===")
                print(container.get("Name"))
                print("\n=== Mounts ===")
                for m in container.get("Mounts", []):
                    print(f"Type: {m.get('Type')}, Source: {m.get('Source')}, Destination: {m.get('Destination')}")
        if err:
            print(f"Error: {err}")

        client.close()
    except Exception as e:
        print(f"An error occurred: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    query()
