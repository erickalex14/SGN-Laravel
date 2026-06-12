import paramiko
import sys

def check_ntp():
    hostname = "YOUR_SERVER_IP"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    commands = [
        "systemctl status systemd-timesyncd || true",
        "systemctl status chrony || true",
        "systemctl status ntp || true",
        "chronyc tracking || true",
        "chronyc sources || true"
    ]

    try:
        # force output to utf-8
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')

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
    check_ntp()
