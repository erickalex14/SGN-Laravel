import paramiko
import sys

def main():
    hostname = 'YOUR_SERVER_IP'
    port = 27619
    username = 'novitecadmin'
    password = 'novi123'
    
    cmd = 'docker ps'
    if len(sys.argv) > 1:
        cmd = sys.argv[1]
        
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(hostname, port=port, username=username, password=password, timeout=10)
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        print("--- STDOUT ---")
        print(stdout.read().decode('utf-8', errors='ignore'))
        
        err = stderr.read().decode('utf-8', errors='ignore')
        if err:
            print("--- STDERR ---")
            print(err)
            
    except Exception as e:
        print(f"Error connecting: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    main()
