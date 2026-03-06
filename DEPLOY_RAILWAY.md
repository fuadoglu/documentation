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

## 4) APP env-lər

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
LARAVEL_STORAGE_PATH=/data/storage
RUN_MIGRATIONS_ON_BOOT=false

ALLOWED_LOGIN_DOMAIN=company.az
ADMIN_EMAIL=admin@company.az
ADMIN_PASSWORD=<strong-password>
```

`APP_KEY` üçün iki seçim var:

```bash
php artisan key:generate --show
```

çıxan dəyəri Railway `APP_KEY` kimi əlavə edin (tövsiyə olunur).

Alternativ olaraq `APP_KEY` boş buraxılsa, entrypoint script onu avtomatik yaradır və
`$LARAVEL_STORAGE_PATH/framework/runtime_app_key` faylına yazır.

## 5) Persistent volume (mütləq tövsiyə olunur)

Sənəd əlavələri və branding faylları itirilməsin deyə Railway service-ə volume qoşun:

1. Service daxilində `Volumes` bölməsinə keçin.
2. Yeni volume yaradın və mount path olaraq `/data` təyin edin.
3. Env-də `LARAVEL_STORAGE_PATH=/data/storage` saxlayın.

Bu halda storage faylları (o cümlədən auto-generated `APP_KEY`) redeploy/restart zamanı qalacaq.

## 6) Deploy zamanı nə olur

`scripts/railway-entrypoint.sh` bunları icra edir:

1. `php artisan optimize:clear`
2. `php artisan storage:link`
3. `APP_KEY` boşdursa auto-generate və storage altında persist
4. DB hazır olmayanda migrate retry (`MIGRATE_MAX_ATTEMPTS`, `MIGRATE_RETRY_SLEEP_SECONDS`)
5. `RUN_MIGRATIONS_ON_BOOT=true` olduqda `php artisan migrate --force`
6. Opsional seed (`RUN_DB_SEED=true` olduqda)
7. `php artisan config:cache`
8. `php artisan route:clear` (default)
9. `php artisan view:cache`
10. `php artisan serve --host=0.0.0.0 --port=$PORT`

Qeyd:
- Bu layihədə `health: /up` closure route istifadə etdiyi üçün `route:cache` bəzi hallarda fail verə bilər.
- İstəsəniz `ENABLE_ROUTE_CACHE=true` ilə aktiv edə bilərsiniz; fail olarsa script avtomatik `route:clear`-a düşür.
- Railway-də stabil startup üçün default tövsiyə: `RUN_MIGRATIONS_ON_BOOT=false`
- Migrate-i manual bir dəfə `Railway Shell` daxilində işlədin: `php artisan migrate --force`

## 7) İlk deploydan sonra yoxlama

1. `GET /up` - healthcheck `200` qaytarmalıdır.
2. `GET /login` açılmalıdır.
3. Admin hesabı ilə login yoxlanmalıdır.
4. `public/build/manifest.json` error-u olmamalıdır.
5. Fayl upload edib service restart sonrası faylın qaldığını yoxlayın.

## 8) Tez-tez rastlanan problemlər

### Vite manifest not found
Səbəb: frontend build edilməyib.  
Bu repo Docker build zamanı `npm run build` çalışdırır və problemi aradan qaldırır.

### APP_KEY is not set
Səbəb: Köhnə build/deploy və ya fərqli entrypoint versiyası.  
Həll: Son commit-i deploy edin. Bu versiyada `APP_KEY` boş olsa belə script onu avtomatik yaradır.

### 500 / DB connection
Səbəb: MySQL env-ləri düzgün map edilməyib.  
Həll: `DB_*` dəyişənlərini Railway MySQL service dəyərlərinə bağlayın.

### Fayllar restartdan sonra itir
Səbəb: persistent volume qoşulmayıb və ya `LARAVEL_STORAGE_PATH` təyin edilməyib.  
Həll: `/data` mount edin və `LARAVEL_STORAGE_PATH=/data/storage` əlavə edin.
