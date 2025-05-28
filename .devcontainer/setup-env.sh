#!/bin/bash

# Step 1: Copy .env.example to .env if .env doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created from .env.example"
else
    echo ".env file already exists, skipping creation."
fi

# Step 2: Generate a new APP_KEY if it's not already set
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    echo "APP_KEY not set or empty, generating a new key..."
    php artisan key:generate
    echo "APP_KEY generated."
else
    echo "APP_KEY already set."
fi

# Step 3: Set default MariaDB credentials for Codespaces
if ! grep -q "^DB_CONNECTION=" .env; then
    echo "DB_CONNECTION=mysql" >> .env
    echo "DB_HOST=db" >> .env
    echo "DB_PORT=3306" >> .env
    echo "DB_DATABASE=laravel" >> .env
    echo "DB_USERNAME=root" >> .env
    echo "DB_PASSWORD=password" >> .env
else
    # Replace existing DB_PASSWORD with DB_PASSWORD=password
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=password/' .env
fi

# Step 4: Set domain and related URLs based on Codespaces environment
if [ -n "$CODESPACE_NAME" ]; then
    CODESPACE_URL="$CODESPACE_NAME-8000.app.github.dev"
    # Replace APP_URL with Codespace URL
    if grep -q "^APP_URL=" .env; then
        sed -i "s|^APP_URL=.*|APP_URL=https://$CODESPACE_URL:8000|" .env
    else
        echo "APP_URL=https://$CODESPACE_URL:8000" >> .env
    fi
    # Replace SANCTUM_STATEFUL_DOMAINS with Codespace domain and allertavvf.test
    if grep -q "^SANCTUM_STATEFUL_DOMAINS=" .env; then
        sed -i "s|^SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$CODESPACE_URL:8000,allertavvf.test|" .env
    else
        echo "SANCTUM_STATEFUL_DOMAINS=$CODESPACE_URL:8000,allertavvf.test" >> .env
    fi
fi

echo "Environment variables have been set up successfully. Please check your .env file."
echo "Setup complete. Your Codespace is ready!"
