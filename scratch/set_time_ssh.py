import paramiko
import sys
import datetime
import time

def set_time_and_timezone():
    hostname = "181.198.104.181"
    port = 27619
    username = "novitecadmin"
    password = "novi123"

    # Get local machine current time
    local_now = datetime.datetime.now()
    time_str = local_now.strftime("%Y-%m-%d %H:%M:%S")
    print(f"Local machine current time: {time_str}")

    try:
        sys.stdout.reconfigure(encoding='utf-8')
        client = paramiko.SSHClient()
        client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        client.connect(hostname, port=port, username=username, password=password, timeout=10)

        def run_sudo_cmd(cmd):
            print(f"Executing sudo: {cmd}")
            # Use sudo -S to pass password via stdin
            stdin, stdout, stderr = client.exec_command(f"sudo -S {cmd}")
            stdin.write(password + '\n')
            stdin.flush()
            out = stdout.read().decode('utf-8', errors='replace').strip()
            err = stderr.read().decode('utf-8', errors='replace').strip()
            # Filter out the [sudo] password prompt from stderr
            err_lines = [line for line in err.splitlines() if "[sudo] password for" not in line]
            err_filtered = "\n".join(err_lines).strip()
            if out:
                print(f"Output:\n{out}")
            if err_filtered:
                print(f"Error:\n{err_filtered}")
            print("-" * 40)

        # 1. Set host timezone to America/Guayaquil
        run_sudo_cmd("timedatectl set-timezone America/Guayaquil")

        # 2. Disable NTP so we can manually set the clock
        run_sudo_cmd("timedatectl set-ntp false")

        # 3. Set the system clock manually to the local machine time
        # Get local time again to be as precise as possible
        precise_now = datetime.datetime.now()
        precise_time_str = precise_now.strftime("%Y-%m-%d %H:%M:%S")
        run_sudo_cmd(f'date -s "{precise_time_str}"')

        # 4. Write system clock to hardware clock
        run_sudo_cmd("hwclock -w")

        # 5. Show timedatectl status
        run_sudo_cmd("timedatectl")

        # 6. Verify time inside the docker container
        print("Executing: docker exec novitec-sgn date")
        stdin, stdout, stderr = client.exec_command("docker exec novitec-sgn date")
        print(f"Docker date: {stdout.read().decode('utf-8').strip()}")
        print("-" * 40)

        # 7. Verify Carbon America/Guayaquil time inside container
        print("Executing: PHP Carbon America/Guayaquil test inside container")
        cmd_carbon = "docker exec novitec-sgn php -r \"require 'vendor/autoload.php'; echo \\Carbon\\Carbon::now('America/Guayaquil')->toDateTimeString().PHP_EOL;\""
        stdin, stdout, stderr = client.exec_command(cmd_carbon)
        print(f"Carbon time: {stdout.read().decode('utf-8').strip()}")
        print("-" * 40)

        # 8. Restart containers to make sure they pick up any configuration cache
        print("Restarting docker containers...")
        run_sudo_cmd("docker restart novitec-sgn novitec-web")

        client.close()
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    set_time_and_timezone()
