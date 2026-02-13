import socket
from urllib.parse import urlparse
import argparse

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--url", required=True)
    parser.add_argument("--remote-file", required=True)
    args = parser.parse_args()

    url = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80
    
    filename = args.remote_file.split("/")[-1]
    path = f"/upload/{filename}"

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

    parts = response.split(b"\r\n\r\n", 1)
    if len(parts) > 1:
        image_data = parts[1]
        print("Tai file thanh cong.")
        print(f"Kich thuoc file anh: {len(image_data)} bytes")
    else:
        print("Loi khi download file")

if __name__ == "__main__":
    main()