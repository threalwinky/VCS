import socket
import os
from urllib.parse import urlparse
import argparse

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", required=True)
    parser.add_argument("--user", required=True)
    parser.add_argument("--password", required=True)
    parser.add_argument("--local-file", required=True)
    args = parser.parse_args()

    url = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80

    with open(args.local_file, "rb") as f:
        file_data = f.read()

    request_header = (
        f"POST /upload HTTP/1.1\r\n"
        f"Host: {host}\r\n"
        f"Content-Type: application/octet-stream\r\n"
        f"Content-Length: {len(file_data)}\r\n"
        f"Connection: close\r\n\r\n"
    )

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((host, port))
    s.sendall(request_header.encode() + file_data)

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