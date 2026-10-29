import http.server
import socketserver
import json
import csv
import os
from datetime import datetime

PORT = 5500
BASE_DIR = os.path.abspath(os.path.dirname(__file__))

class AppRequestHandler(http.server.SimpleHTTPRequestHandler):
    def end_headers(self):
        # Enable CORS so frontend fetch works without restrictions
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        super().end_headers()

    def do_OPTIONS(self):
        self.send_response(200, "ok")
        self.end_headers()

    def do_POST(self):
        if self.path == '/api/register':
            content_length = int(self.headers.get('Content-Length', 0))
            post_body = self.rfile.read(content_length)
            
            try:
                data = json.loads(post_body.decode('utf-8'))
            except Exception as e:
                self.send_response(400)
                self.send_header('Content-Type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps({'status': 'error', 'message': 'Invalid JSON'}).encode('utf-8'))
                return

            # Extract fields
            name = data.get('name', '').strip()
            email = data.get('email', '').strip()
            mobile = data.get('mobile', '').strip()
            course = data.get('course', '').strip()
            year = data.get('year', '').strip()
            gender = data.get('gender', '').strip()
            timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            reg_id = 'REG-' + os.urandom(3).hex().upper()

            # Ensure data directories exist
            data_dir_1 = os.path.join(BASE_DIR, 'data')
            data_dir_2 = os.path.join(BASE_DIR, 'php-form-handling', 'data')
            os.makedirs(data_dir_1, exist_ok=True)
            os.makedirs(data_dir_2, exist_ok=True)

            # Target CSV files
            csv_targets = [
                os.path.join(data_dir_1, 'registrations.csv'),
                os.path.join(data_dir_2, 'registrations.csv')
            ]

            # 1. Append to CSV files
            for csv_file in csv_targets:
                file_exists = os.path.exists(csv_file) and os.path.getsize(csv_file) > 0
                with open(csv_file, mode='a', newline='', encoding='utf-8') as f:
                    writer = csv.writer(f)
                    if not file_exists:
                        writer.writerow(['Registration ID', 'Timestamp', 'Full Name', 'Email', 'Phone', 'Course', 'Year', 'Gender'])
                    writer.writerow([reg_id, timestamp, name, email, mobile, course, year, gender])

            # Target JSON files
            json_targets = [
                os.path.join(data_dir_1, 'registrations.json'),
                os.path.join(data_dir_2, 'registrations.json')
            ]

            # 2. Append to JSON files
            for json_file in json_targets:
                records = []
                if os.path.exists(json_file) and os.path.getsize(json_file) > 0:
                    try:
                        with open(json_file, 'r', encoding='utf-8') as jf:
                            records = json.load(jf)
                    except:
                        records = []
                records.append({
                    'registrationId': reg_id,
                    'timestamp': timestamp,
                    'fullName': name,
                    'email': email,
                    'phone': mobile,
                    'course': course,
                    'year': year,
                    'gender': gender
                })
                with open(json_file, 'w', encoding='utf-8') as jf:
                    json.dump(records, jf, indent=2)

            print(f"[*] Saved Registration: {reg_id} - {name} to CSV and JSON.")

            # Send JSON response
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            response = {
                'status': 'success',
                'registrationId': reg_id,
                'message': f'Registration saved to CSV and JSON! ID: {reg_id}'
            }
            self.wfile.write(json.dumps(response).encode('utf-8'))
        else:
            self.send_response(404)
            self.end_headers()

if __name__ == '__main__':
    os.chdir(BASE_DIR)
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("", PORT), AppRequestHandler) as httpd:
        print(f"[*] StudentHub server running at http://localhost:{PORT}")
        httpd.serve_forever()
