# Perform<sup>[cb]</sup> Task
## Simple access log analyzing app

#### Using Xampp start:
* Apache server on port: 80
* Mysql server on port: 3306

#### php.ini settings:
* memory_limit = 4000M
* post_max_size = 1000M
* upload_max_filesize = 1000M

#### httpd-vhost.conf settings:
* Create virtual host
```
<VirtualHost *:80>
    DocumentRoot "C:\xampp\htdocs\performcb-task\public"
    ServerName localhost

    <Directory "C:\xampp\htdocs\performcb-task\public">
    Options All
    AllowOverride All
    Require all granted
    RewriteEngine On
    
    RewriteCond %{REQUEST_FILENAME} !-f  
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php [L]
    </Directory> 
</VirtualHost>
```
