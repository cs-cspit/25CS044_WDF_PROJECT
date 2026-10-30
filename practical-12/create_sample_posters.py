import os

def create_sample_png(filepath, text):
    # Generates a minimal 1x1 valid PNG binary if PIL is not present
    # Minimal 1x1 red PNG hex data
    minimal_png = (
        b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01'
        b'\x08\x02\x00\x00\x00\x90wS\xde\x00\x00\x00\x0cIDATx\x9cc\xf8\xcf'
        b'\xc0\x00\x00\x03\x01\x01\x00\x18\xdd\x8d\xb0\x00\x00\x00\x00IEND\xaeB`\x82'
    )
    with open(filepath, 'wb') as f:
        f.write(minimal_png)
    print(f"Created poster: {filepath}")

upload_dir = r"D:\25CS044_WDF_PROJECT-main\practical-12\uploads"
posters = ["git_workshop.png", "codeverse.png", "web_seminar.png", "coding_contest.png", "default_poster.png", "valid_poster_sample.png"]

for p in posters:
    p_path = os.path.join(upload_dir, p)
    if not os.path.exists(p_path):
        create_sample_png(p_path, p)
