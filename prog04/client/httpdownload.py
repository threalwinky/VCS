import socket
from urllib.parse import urlparse
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("--url", required=True)
parser.add_argument("--remote-file", required=True)
args = parser.parse_args()

url = urlparse(args.url)
host = url.hostname
port = url.port if url.port else 80

path = "/download?filename=" + args.remote_file

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host, port))

request = (
    f"GET {path} HTTP/1.1\r\n"
    f"Host: {host}\r\n"
    f"Connection: close\r\n\r\n"
)

s.sendall(request.encode())

response = b""
while True:
    data = s.recv(4096)
    if not data:
        break
    response += data

s.close()

parts = response.split(b"\r\n\r\n", 1)
body = parts[1].decode()
print(body)