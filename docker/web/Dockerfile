FROM ghcr.io/thevajko/vaii-web-server:main

# Update balíčkov a nainštaluj PostgreSQL vývojové knižnice
RUN apt-get update && apt-get install -y libpq-dev

# Nainštaluj PDO pre PostgreSQL
RUN docker-php-ext-install pdo_pgsql pgsql
