from flask import *
import os
import uuid
from werkzeug.utils import secure_filename

app = Flask(__name__)

uploads_path = './uploads'

if not os.path.exists(uploads_path):
    os.makedirs(uploads_path)
    
users = {
    'test': 'test123QWE@AD'
}

ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'webp'}

def allowed_file(filename):
    return '.' in filename and \
        filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.get('/')
def get():
    return '''
<!DOCTYPE html>
<html>
<head>
    <title>anhtudsvk4</title>
</head>
<body>
    <h1>test</h1>
</body>
</html>
''', 200

@app.post('/')
def post():
    username = request.form['username']
    password = request.form['password']
    if username not in users:
        return 'Khong tim thay user', 400
    if users[username] != password:
        return f'User {username} dang nhap that bai', 400
    return f'User {username} dang nhap thanh cong', 200

@app.post('/upload')
def upload():
    username = request.form.get('username')
    password = request.form.get('password')
    if not username or not password:
        return 'Thieu username hoac password', 400
    if username not in users:
        return 'Khong tim thay user', 400
    if users[username] != password:
        return f'User khong hop le', 400
    if 'file' not in request.files:
        return 'Khong co file', 40
    file = request.files['file']
    if file.filename == '':
        return 'Ten file khong hop le', 40
    if not allowed_file(file.filename):
        return 'File khong phai la anh hop le', 40
    ext = file.filename.rsplit('.', 1)[1].lower()
    random_name = f"{uuid.uuid4().hex}.{ext}"
    save_path = os.path.join(uploads_path, secure_filename(random_name))
    file.save(save_path)
    return f'Upload thanh cong: {random_name}', 200

@app.get('/download')
def download():
    filename = request.args.get('filename')
    if not filename:
        return 'Khong co filename truyen vao', 400
    safe_name = os.path.basename(filename)
    file_path = os.path.join(uploads_path, safe_name)
    if not os.path.abspath(file_path).startswith(os.path.abspath(uploads_path)):
        return 'Filename khong hop le', 400
    if not os.path.exists(file_path):
        return 'File khong ton tai', 404
    size = os.path.getsize(file_path)
    return f'File {safe_name} co kich thuoc {size} bytes', 200

app.run('0.0.0.0', 5000)