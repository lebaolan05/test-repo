## Project Structure

```
socialnet-app/
├── admin/
│   └── newuser.php          # Admin: create new users
├── socialnet/
│   ├── index.php            # Home page
│   ├── signin.php           # Sign in page
│   ├── signout.php          # Sign out
│   ├── profile.php          # Profile page (?owner=username)
│   ├── setting.php          # Edit profile description
│   ├── about.php            # About page (student info)
│   └── includes/
│       ├── db.php           # PDO database connection
│       ├── auth.php         # Session helpers
│       ├── menubar.php      # Shared navigation bar
│       └── style.css        # Shared stylesheet
├── sql/
│   └── setup.sql            # Database + table creation script
└── nginx/
    └── socialnet.conf       # Nginx server block config
```

## Pages & URLs

| Page       | URL                        | Description                                      |
|------------|----------------------------|--------------------------------------------------|
| Admin      | `/admin/newuser.php`       | Add new user accounts                         |
| Sign In    | `/socialnet/signin.php`    | Login page                                       |
| Home       | `/socialnet/index.php`     | Home + list of other users                  |
| Setting    | `/socialnet/setting.php`   | Edit profile description                         |
| Profile    | `/socialnet/profile.php`   | View own or another user's profile (`?owner=xyz`)|
| About      | `/socialnet/about.php`     | Static page with student name & number           |
| Sign Out   | `/socialnet/signout.php`   | Destroys session, redirects to Sign In           |

## Setup Instructions

### 1. Prerequisites

```bash
sudo apt update
sudo apt install nginx php8.2-fpm php8.2-mysql mysql-server
```

### 2. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/socialnet-app.git /var/www/socialnet-app
```

### 3. Set up the database

```bash
mysql -u root -p < /var/www/socialnet-app/sql/setup.sql
```

Update database credentials in `socialnet/includes/db.php`:
```php
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

### 4. Configure Nginx

```bash
sudo cp /var/www/socialnet-app/nginx/socialnet.conf /etc/nginx/sites-available/socialnet
sudo ln -s /etc/nginx/sites-available/socialnet /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

> **Note:** Check your PHP-FPM socket path in `nginx/socialnet.conf`. 
> Change `php8.2-fpm.sock` to match your installed PHP version.

### 5. Set file permissions

```bash
sudo chown -R www-data:www-data /var/www/socialnet-app
sudo chmod -R 755 /var/www/socialnet-app
```

### 6. Start services

```bash
sudo systemctl start nginx
sudo systemctl start php8.2-fpm
sudo systemctl start mysql
```

### 7. Update About page

Edit `socialnet/about.php` and replace:
- `Your Full Name` → your actual name
- `XXXXXXXX` → your actual student number

### 8. Create first user

Visit: `http://localhost/admin/newuser.php`
