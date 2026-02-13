sudo apt install apache2 mysql-server php8.3 libapache2-mod-php8.3 php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-xmlrpc php8.3-soap php8.3-intl php8.3-zip -y

CREATE DATABASE wordpress_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp_user'@'localhost' IDENTIFIED BY 'Mypassword@123';
GRANT ALL PRIVILEGES ON wordpress_db.* TO 'wp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

cd /tmp
wget https://wordpress.org/latest.tar.gz
tar -xvzf latest.tar.gz
sudo mv wordpress /var/www/web1/html

sudo chown -R www-data:www-data /var/www/web1
sudo chmod -R 755 /var/www/web1