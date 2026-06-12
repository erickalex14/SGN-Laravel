import paramiko
import sys

def diag_network():
    hostname = "181.198.104.181"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    commands = [
        "ping -c 2 8.8.8.8 || echo 'ping 8.8.8.8 failed'",
        "ping -c 2 pool.ntp.org || echo 'ping pool.ntp.org failed'",
        "nslookup pool.ntp.org || dig pool.ntp.org || echo 'dns failed'",
        "curl -I https://www.google.com || echo 'curl failed'"
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
    diag_network()
