import socket
from urllib.parse import urlparse
import argparse
import re

parser = argparse.ArgumentParser()
parser.add_argument("--url", required=True)
args = parser.parse_args()

url = urlparse(args.url)
path = url.path if url.path else "/"
host = url.hostname
port = url.port

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.connect((host, port))

request = (
    f"GET {path} HTTP/1.1\r\n"
    f"Host: {host}\r\n"
    f"Connection: close\r\n\r\n"
).encode()
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

match = re.search(r"<title>(.*?)</title>", body)
if match:
    print(match.group(1).strip())
else:
    print("No title found")