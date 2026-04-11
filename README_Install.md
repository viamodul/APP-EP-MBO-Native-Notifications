# Guia de Deploy - ePages Webhooks

## Requisitos do Servidor

- Ubuntu 22.04+ / Debian 12+
- PHP 8.2+ com extensões: mbstring, xml, curl, mysql, zip, bcmath
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+ ou PostgreSQL 14+
- Nginx
- Supervisor
- Git

## 1. Preparar o Servidor

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependências
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath \
    supervisor git unzip curl

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar Certbot (SSL)
sudo apt install -y certbot python3-certbot-nginx
```

## 2. Criar Base de Dados

```bash
sudo mysql -u root
```

```sql
CREATE DATABASE epages_webhooks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'epages_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURA';
GRANT ALL PRIVILEGES ON epages_webhooks.* TO 'epages_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

Trocar password:
ALTER USER 'epages_user'@'localhost' IDENTIFIED BY 'WWetr$433442#sdaWWetr$433442#sda';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Clonar Projecto

```bash
sudo mkdir -p /var/www/html/APP-EP-MBO-Native-Notifications
sudo chown $USER:www-data /var/www/html/APP-EP-MBO-Native-Notifications
cd /var/www/html
git clone https://github.com/viamodul/APP-EP-MBO-Native-Notifications.git APP-EP-MBO-Native-Notifications
cd APP-EP-MBO-Native-Notifications
```

## 4. Configurar Aplicação

```bash
# Instalar dependências
composer install --no-dev --optimize-autoloader

# Copiar e editar .env
cp deploy/.env.production.example .env
nano .env  # Configurar todas as variáveis

# Gerar APP_KEY
php artisan key:generate

# Executar migrations
php artisan migrate --force

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm ci
npm run build

# Permissões
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 5. Configurar Nginx

```bash
# Copiar config
sudo cp deploy/nginx.conf /etc/nginx/sites-available/epageswebhooksapp.viamodul.eu

# Editar domínio
sudo nano /etc/nginx/sites-available/epageswebhooksapp.viamodul.eu
# Substituir SEU_DOMINIO.com pelo domínio real

# Activar site
sudo ln -s /etc/nginx/sites-available/epageswebhooksapp.viamodul.eu /etc/nginx/sites-enabled/

# Remover default (opcional)
sudo rm /etc/nginx/sites-enabled/default

# Testar e reiniciar
sudo nginx -t
sudo systemctl restart nginx
```

## 6. SSL com Let's Encrypt

```bash
sudo certbot --nginx -d epageswebhooksapp.viamodul.eu
```

## 7. Configurar Supervisor (Queue Worker)

```bash
# Copiar config
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/epageswebhooksapp.viamodul.eu.conf

# Recarregar
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start epages-webhooks-worker:*
```

## 8. Configurar Cron (Scheduler)

```bash
sudo crontab -u www-data -e
```

Adicionar:
```
* * * * * cd /var/www/html/APP-EP-MBO-Native-Notifications && php artisan schedule:run >> /dev/null 2>&1
```

## 9. Stripe Produção

1. Ir a https://dashboard.stripe.com (modo Live)
2. Copiar chaves pk_live_* e sk_live_* para .env
3. Criar webhook endpoint:
   - URL: `https://epageswebhooksapp.viamodul.eu/stripe/webhook`
   - Eventos:
     - customer.subscription.created
     - customer.subscription.updated
     - customer.subscription.deleted
     - invoice.payment_succeeded
4. Copiar Signing Secret para STRIPE_WEBHOOK_SECRET
5. Criar produtos: `php artisan stripe:setup-products`
6. Adicionar Price IDs ao .env

## 10. Verificar

```bash
# Verificar queue workers
sudo supervisorctl status

# Verificar logs
tail -f /var/www/html/APP-EP-MBO-Native-Notifications/storage/logs/laravel.log

# Testar scheduler
php artisan schedule:list
```

## Comandos Úteis

```bash
# Deploy manual
cd /var/www/epages-webhooks && sudo -u www-data ./deploy/deploy.sh

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Restart workers
sudo supervisorctl restart epages-webhooks-worker:*

# Limpar caches
php artisan optimize:clear

# Definir utilizador como Dev (sem limites)
php artisan user:set-tier email@exemplo.com dev
```

## Troubleshooting

### Erro 502 Bad Gateway
- Verificar se PHP-FPM está a correr: `sudo systemctl status php8.2-fpm`
- Verificar socket: `ls -la /var/run/php/php8.2-fpm.sock`

### Queue não processa
- Verificar supervisor: `sudo supervisorctl status`
- Ver logs: `tail -f storage/logs/worker.log`

### Permissões
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```
