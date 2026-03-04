# ECO DC - Railway Deploy Təlimatı

Bu sənəd layihəni Railway üzərində Docker ilə sabit şəkildə yayımlamaq üçündür.

## 1) Repository tələbi

Railway üçün bu fayllar repository-də olmalıdır:

- `Dockerfile`
- `railway.json`
- `scripts/railway-entrypoint.sh`
- `public/build/manifest.json` build zamanı Docker daxilində generasiya olunur.

## 2) Railway layihəsinin yaradılması

1. Railway-də yeni layihə açın.
2. `Deploy from GitHub repo` seçin və bu repository-ni bağlayın.
3. Railway avtomatik `Dockerfile` ilə build edəcək.

## 3) Database əlavəsi

Railway layihəsinə `MySQL` servisi əlavə edin.

Sonra web service üçün aşağıdakı env dəyişənləri təyin edin:

```env
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

## 4) Mütləq APP env-lər

Web service dəyişənləri:

```env
APP_NAME=ECO DC
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-railway-domain>

APP_LOCALE=az
APP_FALLBACK_LOCALE=en
APP_AVAILABLE_LOCALES=az,en
APP_TIMEZONE=Asia/Baku

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

ALLOWED_LOGIN_DOMAIN=company.az
ADMIN_EMAIL=admin@company.az
ADMIN_PASSWORD=<strong-password>
```

`APP_KEY` üçün:

```bash
php artisan key:generate --show
```

çıxan dəyəri Railway `APP_KEY` kimi əlavə edin.

## 5) Deploy zamanı nə olur

`scripts/railway-entrypoint.sh` bunları icra edir:

1. `php artisan optimize:clear`
2. `php artisan storage:link`
3. `php artisan migrate --force`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `php artisan view:cache`
7. `php artisan serve --host=0.0.0.0 --port=$PORT`

## 6) İlk deploydan sonra yoxlama

1. `GET /up` - healthcheck `200` qaytarmalıdır.
2. `GET /login` açılmalıdır.
3. Admin hesabı ilə login yoxlanmalıdır.
4. `public/build/manifest.json` error-u olmamalıdır.

## 7) Tez-tez rastlanan problemlər

### Vite manifest not found
Səbəb: frontend build edilməyib.  
Bu repo Docker build zamanı `npm run build` çalışdırır və problemi aradan qaldırır.

### APP_KEY is not set
Səbəb: Railway env-də `APP_KEY` yoxdur.  
Həll: `php artisan key:generate --show` ilə dəyər yaradıb env-ə əlavə edin.

### 500 / DB connection
Səbəb: MySQL env-ləri düzgün map edilməyib.  
Həll: `DB_*` dəyişənlərini Railway MySQL service dəyərlərinə bağlayın.
