# Docker Deployment - Menambahkan Environment Variables

## Metode 1: Menggunakan docker-compose.yml (RECOMMENDED)

Jika production menggunakan Docker Compose, tambahkan environment variable di `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    image: your-laravel-app:latest
    container_name: paud-app
    environment:
      - APP_NAME=Laravel
      - APP_ENV=production
      - APP_KEY=${APP_KEY}
      - APP_DEBUG=false
      - APP_URL=https://your-domain.com

      # Database
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=paud_db
      - DB_USERNAME=paud_user
      - DB_PASSWORD=${DB_PASSWORD}

      # Gemini AI API Key
      - GEMINI_API_KEY=${GEMINI_API_KEY}

    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache

    networks:
      - paud-network

    depends_on:
      - db

  db:
    image: mariadb:10.11
    container_name: paud-db
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MYSQL_DATABASE=paud_db
      - MYSQL_USER=paud_user
      - MYSQL_PASSWORD=${DB_PASSWORD}
    volumes:
      - db-data:/var/lib/mysql
    networks:
      - paud-network

volumes:
  db-data:

networks:
  paud-network:
```

### Buat file `.env` di server production:

```bash
# File: .env (di folder yang sama dengan docker-compose.yml)
APP_KEY=base64:your_app_key_here
DB_PASSWORD=your_secure_db_password
DB_ROOT_PASSWORD=your_secure_root_password
GEMINI_API_KEY=your_gemini_api_key_here
```

### Deploy:
```bash
# Pull image terbaru
docker-compose pull

# Start services
docker-compose up -d

# Atau restart jika sudah running
docker-compose restart app
```

---

## Metode 2: Menggunakan `docker run` dengan `-e` flag

Jika menjalankan container langsung dengan `docker run`:

```bash
docker run -d \
  --name paud-app \
  -p 8000:8000 \
  -e APP_NAME=Laravel \
  -e APP_ENV=production \
  -e APP_KEY=base64:your_app_key_here \
  -e APP_DEBUG=false \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=mysql-container \
  -e DB_PORT=3306 \
  -e DB_DATABASE=paud_db \
  -e DB_USERNAME=paud_user \
  -e DB_PASSWORD=your_db_password \
  -e GEMINI_API_KEY=your_gemini_api_key_here \
  your-laravel-app:latest
```

---

## Metode 3: Menggunakan `--env-file`

Buat file `.env.production` di server:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=paud_db
DB_USERNAME=paud_user
DB_PASSWORD=your_secure_password

GEMINI_API_KEY=your_gemini_api_key_here
```

Jalankan dengan:
```bash
docker run -d \
  --name paud-app \
  --env-file .env.production \
  your-laravel-app:latest
```

Atau dengan Docker Compose:
```yaml
services:
  app:
    image: your-laravel-app:latest
    env_file:
      - .env.production
```

---

## Metode 4: Menggunakan Docker Secrets (PALING AMAN)

Untuk production yang lebih aman, gunakan Docker Secrets (khusus Docker Swarm):

### 1. Buat secret:
```bash
# Buat secret dari file
echo "your_gemini_api_key_here" | docker secret create gemini_api_key -

# Atau dari file
docker secret create gemini_api_key gemini_api_key.txt
```

### 2. Update docker-compose.yml:
```yaml
version: '3.8'

services:
  app:
    image: your-laravel-app:latest
    secrets:
      - gemini_api_key
      - db_password
    environment:
      - GEMINI_API_KEY_FILE=/run/secrets/gemini_api_key
    deploy:
      replicas: 1

secrets:
  gemini_api_key:
    external: true
  db_password:
    external: true
```

### 3. Modifikasi kode untuk baca dari file secret:
```php
// config/services.php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY')
        ?: (file_exists(env('GEMINI_API_KEY_FILE'))
            ? trim(file_get_contents(env('GEMINI_API_KEY_FILE')))
            : null),
],
```

---

## Metode 5: Update Environment Variable di Container yang Sudah Running

### A. Menggunakan `docker exec`:
```bash
# Masuk ke container
docker exec -it paud-app bash

# Edit file .env di dalam container
nano /var/www/html/.env

# Tambahkan:
# GEMINI_API_KEY=your_api_key_here

# Clear cache
php artisan config:clear

# Exit
exit

# Restart container
docker restart paud-app
```

### B. Menggunakan `docker-compose`:
```bash
# Edit docker-compose.yml, tambahkan GEMINI_API_KEY

# Recreate container dengan config baru
docker-compose up -d --force-recreate app

# Atau restart
docker-compose restart app
```

---

## Metode 6: Menggunakan Portainer (GUI)

Jika menggunakan Portainer untuk manage Docker:

1. Login ke Portainer web interface
2. Pilih **Containers** → Klik container `paud-app`
3. Klik **Duplicate/Edit**
4. Scroll ke **Environment variables**
5. Klik **+ Add environment variable**
6. Name: `GEMINI_API_KEY`
7. Value: `your_api_key_here`
8. Klik **Deploy the container**

---

## Best Practices untuk Production

### 1. **Jangan Hardcode API Key di Image**
❌ JANGAN:
```dockerfile
ENV GEMINI_API_KEY=AIzaSy... # JANGAN HARDCODE!
```

✅ LAKUKAN:
```yaml
# docker-compose.yml
environment:
  - GEMINI_API_KEY=${GEMINI_API_KEY}
```

### 2. **Gunakan .env File yang Secure**
```bash
# Di server production
chmod 600 .env  # Hanya owner yang bisa read/write
chown root:root .env  # Owner adalah root
```

### 3. **Jangan Commit .env ke Git**
Pastikan `.gitignore` include:
```gitignore
.env
.env.production
.env.*.local
```

### 4. **Gunakan CI/CD untuk Inject Secrets**

#### GitLab CI/CD:
```yaml
# .gitlab-ci.yml
deploy:
  stage: deploy
  script:
    - echo "GEMINI_API_KEY=$GEMINI_API_KEY" >> .env
    - docker-compose up -d
  only:
    - main
```

#### GitHub Actions:
```yaml
# .github/workflows/deploy.yml
- name: Deploy
  env:
    GEMINI_API_KEY: ${{ secrets.GEMINI_API_KEY }}
  run: |
    echo "GEMINI_API_KEY=$GEMINI_API_KEY" >> .env
    docker-compose up -d
```

### 5. **Verify Environment Variable Loaded**
```bash
# Check di dalam container
docker exec paud-app php artisan tinker

# Di tinker:
>>> config('services.gemini.api_key')
=> "AIzaSy..."  // Harus muncul API key

>>> env('GEMINI_API_KEY')
=> "AIzaSy..."
```

---

## Troubleshooting

### Problem: Environment variable tidak terbaca di container

**Solution 1: Clear config cache**
```bash
docker exec paud-app php artisan config:clear
docker exec paud-app php artisan cache:clear
```

**Solution 2: Restart container**
```bash
docker restart paud-app
# atau
docker-compose restart app
```

**Solution 3: Recreate container**
```bash
docker-compose up -d --force-recreate app
```

### Problem: API key tidak work setelah ditambahkan

**Check 1: Verify env var ada di container**
```bash
docker exec paud-app env | grep GEMINI
# Output: GEMINI_API_KEY=AIzaSy...
```

**Check 2: Verify Laravel bisa baca**
```bash
docker exec paud-app php -r "echo getenv('GEMINI_API_KEY');"
```

**Check 3: Check logs**
```bash
docker logs paud-app
# atau
docker exec paud-app tail -f storage/logs/laravel.log
```

---

## Quick Reference Commands

```bash
# Lihat environment variables di container
docker exec paud-app env

# Edit .env di container
docker exec -it paud-app nano /var/www/html/.env

# Clear cache after changing env
docker exec paud-app php artisan config:clear

# Restart container
docker restart paud-app

# Recreate container with new env
docker-compose up -d --force-recreate app

# Check if env loaded
docker exec paud-app php artisan tinker --execute="echo config('services.gemini.api_key');"
```

---

## Rekomendasi untuk Production

**Pilihan terbaik berdasarkan setup:**

1. **Small VPS / Single Server**: Gunakan **Metode 1** (docker-compose.yml dengan .env file)
2. **Team dengan CI/CD**: Inject secrets via **GitLab CI** atau **GitHub Actions**
3. **High Security**: Gunakan **Metode 4** (Docker Secrets)
4. **Managed Hosting (Railway, Heroku, etc)**: Set via **platform dashboard**

Untuk kebanyakan kasus, **Metode 1 dengan docker-compose.yml** adalah yang paling praktis dan mudah di-maintain! 🚀
