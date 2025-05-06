FROM php:7.4-cli

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto Laravel
COPY . .

# Instala as dependências
RUN composer install --no-dev --optimize-autoloader

# Garante permissões para storage e cache
RUN chmod -R 755 storage bootstrap/cache

# Expõe a porta padrão do Laravel
EXPOSE 8000

# Inicia o servidor embutido do Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
