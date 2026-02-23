import socket
from urllib.parse import urlparse
import argparse
import uuid
import os
import sys

parser = argparse.ArgumentParser()
parser.add_argument("--url", required=True)
parser.add_argument("--user", required=True)
parser.add_argument("--password", required=True)
parser.add_argument("--local-file", required=True)
args = parser.parse_args()

url = urlparse(args.url)
path = '/upload'
host = url.hostname
port = url.port if url.port else 80

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host, port))

boundary = uuid.uuid4().hex
filename = os.path.basename(args.local_file)

if not os.path.isfile(args.local_file):
    print("File khong ton tai")
    sys.exit(1)

with open(args.local_file, "rb") as f:
    file_content = f.read()

body = (
    f"--{boundary}\r\n"
    f'Content-Disposition: form-data; name="username"\r\n\r\n'
    f"{args.user}\r\n"
    f"--{boundary}\r\n"
    f'Content-Disposition: form-data; name="password"\r\n\r\n'
    f"{args.password}\r\n"
    f"--{boundary}\r\n"
    f'Content-Disposition: form-data; name="file"; filename="{filename}"\r\n'
    f"Content-Type: application/octet-stream\r\n\r\n"
).encode() + file_content + f"\r\n--{boundary}--\r\n".encode()

request = (
    f"POST {path} HTTP/1.1\r\n"
    f"Host: {host}\r\n"
    f"Content-Type: multipart/form-data; boundary={boundary}\r\n"
    f"Content-Length: {len(body)}\r\n"
    f"Connection: close\r\n\r\n"
).encode() + body

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