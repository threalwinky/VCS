import socket
from urllib.parse import urlparse
import argparse

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", required=True)
    args = parser.parse_args()

    url = urlparse(args.url)
    path = url.path if url.path else "/"
    host = url.hostname
    port = url.port if url.port else 80

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((host, port))
    
    request = f"GET {path} HTTP/1.1\r\nHost: {host}\r\nConnection: close\r\n\r\n"
    s.sendall(request.encode())

    response = b""
    while True:
        data = s.recv(4096)
        if not data: break
        response += data
    s.close()

    body = response.split(b"\r\n\r\n")[1].decode()
    print(body)

if __name__ == "__main__":
    main()