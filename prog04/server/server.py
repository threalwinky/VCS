import socket
import os
import uuid
import re

HOST = '127.0.0.1'
PORT = 8080
UPLOAD_FOLDER = 'uploads'
USERS = {'test': 'test123QWE@AD'}

if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

def create_response(status_code, content_type, body):
    status_text = "200 OK" if status_code == 200 else "404 Not Found"
    if status_code == 401: status_text = "401 Unauthorized"
    
    response = f"HTTP/1.1 {status_text}\r\n"
    response += f"Content-Type: {content_type}\r\n"
    response += f"Content-Length: {len(body)}\r\n"
    response += "Connection: close\r\n"
    response += "\r\n"
    return response.encode() + (body if isinstance(body, bytes) else body.encode())

def handle_client(client_socket):
    request_data = b""
    while b"\r\n\r\n" not in request_data:
        chunk = client_socket.recv(4096)
        if not chunk: break
        request_data += chunk

    if not request_data:
        client_socket.close()
        return

    header_part = request_data.split(b"\r\n\r\n")[0].decode()
    lines = header_part.split("\r\n")
    method, path, _ = lines[0].split(" ")
    
    content_length = 0
    for line in lines:
        if "Content-Length:" in line:
            content_length = int(line.split(":")[1].strip())

    body = request_data.split(b"\r\n\r\n")[1]
    while len(body) < content_length:
        body += client_socket.recv(4096)

    if method == "GET" and path == "/":
        client_socket.sendall(create_response(200, "text/plain", "ok"))

    elif method == "POST" and path == "/login":
        body_str = body.decode(errors='ignore')
        user_match = re.search(r'username=([^&]+)', body_str)
        pass_match = re.search(r'password=([^&]+)', body_str)
        
        if user_match and pass_match:
            u, p = user_match.group(1), pass_match.group(1)
            if USERS.get(u) == p:
                client_socket.sendall(create_response(200, "text/plain", "Login successfully"))
            else:
                client_socket.sendall(create_response(401, "text/plain", "Invalid credentials"))
        else:
            client_socket.sendall(create_response(400, "text/plain", "Missing fields"))

    elif method == "POST" and path == "/upload":
        if len(body) > 0:
            file_uuid = str(uuid.uuid4())
            file_path = os.path.join(UPLOAD_FOLDER, file_uuid)
            with open(file_path, "wb") as f:
                f.write(body)
            client_socket.sendall(create_response(200, "text/plain", file_uuid))
        else:
            client_socket.sendall(create_response(400, "text/plain", "No image content"))

    elif method == "GET" and path.startswith("/upload/"):
        file_uuid = path.split("/")[-1]
        file_path = os.path.join(UPLOAD_FOLDER, file_uuid)
        if os.path.exists(file_path):
            with open(file_path, "rb") as f:
                img_data = f.read()
            client_socket.sendall(create_response(200, "image/jpeg", img_data))
        else:
            client_socket.sendall(create_response(404, "text/plain", "File not found"))

    else:
        client_socket.sendall(create_response(404, "text/plain", "Not Found"))

    client_socket.close()

def start_server():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server.bind((HOST, PORT))
    server.listen(5)
    print(f"Server starting on http://{HOST}:{PORT}")

    try:
        while True:
            client, addr = server.accept()
            handle_client(client)
    except KeyboardInterrupt:
        pass
    finally:
        server.close()

if __name__ == "__main__":
    start_server()