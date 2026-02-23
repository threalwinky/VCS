import socket
from urllib.parse import urlparse
import argparse
import re

parser = argparse.ArgumentParser()
parser.add_argument("--url", required=True)
parser.add_argument("--user", required=True)
parser.add_argument("--password", required=True)
args = parser.parse_args()

url = urlparse(args.url)
path = url.path if url.path else "/"
host = url.hostname
port = url.port

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host, port))

req_body = (f"username={args.user}&password={args.password}").encode()

request = (
    f"POST {path} HTTP/1.1\r\n"
    f"Host: {host}\r\n"
    f"Content-Type: application/x-www-form-urlencoded\r\n"
    f"Content-Length: {len(req_body)}\r\n"
    f"Connection: close\r\n\r\n"
).encode() + req_body

s.sendall(request)

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