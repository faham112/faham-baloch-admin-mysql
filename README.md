# FAHAM BALOCH Admin Panel (PHP + MySQL)

Hostinger ready Admin Panel for license key approval.

## Setup on Hostinger

1. Create MySQL database
2. Import `database.sql` via phpMyAdmin
3. Edit `config.php` with your DB credentials
4. Upload all files to `public_html` or subdomain folder
5. Point subdomain `admin.globalcareerhub.org` to the folder

## Login
- Username: `admin`
- Password: `faham123`

## API Endpoints
- `POST /api/validate` - License validation
- `POST /api/posts` - Log posts
- `GET/PATCH /api/licenses` - Manage keys

## Tool Config
In `faham_baloch.py`:
```python
API_BASE = "https://admin.globalcareerhub.org/"
```
