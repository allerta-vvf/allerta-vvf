#!/bin/bash

# Check if .env file exists and is empty
if grep -q "DB_HOST=" .env; then
    echo ".env file already exists and is not empty."
else
    # Copy .env.example to .env
    echo "Copying .env.example to .env..."
    cat .env.example > .env

    # Set database credentials
    cp .env .env.tmp
    echo "Setting database credentials..."
    sed -i "s/DB_HOST=127.0.0.1/DB_HOST=${DB_HOST}/g" .env.tmp
    sed -i "s/DB_DATABASE=laravel/DB_DATABASE=${DB_DATABASE}/g" .env.tmp
    sed -i "s/DB_USERNAME=root/DB_USERNAME=${DB_USER}/g" .env.tmp
    sed -i "s/DB_PASSWORD=/DB_PASSWORD=${DB_PASSWORD}/g" .env.tmp

    # Set encryption key
    echo "Generating encryption key..."
    # We can't use just the command, so we need to save the output to a file and then overwrite it in .env.test
    php artisan key:generate --show > key.tmp
    sed -i "s#APP_KEY=#APP_KEY=$(cat key.tmp)#g" .env.tmp
    rm key.tmp

    # Overwrite .env with the updated .env.tmp
    cat .env.tmp > .env
    rm .env.tmp

    # Run migrations
    echo "Running migrations..."
    php artisan migrate --force

    # Run seeders
    echo "Running seeders..."
    php artisan db:seed
    php artisan db:seed --class=DummyDataSeeder
fi

# Run migrations
echo "Running migrations..."
php artisan migrate

# Run Apache
apache2-foreground
