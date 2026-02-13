import socket
from urllib.parse import urlparse
import argparse

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", required=True)
    parser.add_argument("--user", required=True)
    parser.add_argument("--password", required=True)
    args = parser.parse_args()

    url = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80
    
    body = f"username={args.user}&password={args.password}"
    
    request = (
        f"POST /login HTTP/1.1\r\n"
        f"Host: {host}\r\n"
        f"Content-Type: application/x-www-form-urlencoded\r\n"
        f"Content-Length: {len(body)}\r\n"
        f"Connection: close\r\n\r\n"
        f"{body}"
    )

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((host, port))
    s.sendall(request.encode())

    response = b""
    while True:
        data = s.recv(4096)
        if not data: break
        response += data
    s.close()

    if b"200 OK" in response and b"Login successfully" in response:
        print("Dang nhap thanh cong")
    else:
        print("Dang nhap that bai")

if __name__ == "__main__":
    main()