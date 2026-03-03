# ECO DC - Shared Hosting Deploy Təlimatı

Bu sənəd layihəni shared hosting mühitində stabil və təhlükəsiz yayımlamaq üçün addım-addım təlimat verir.

## 1. Server Tələbləri

- PHP `8.2+`
- MySQL `8+` və ya MariaDB `10.6+`
- PHP extension-lar:
  - `bcmath`
  - `ctype`
  - `fileinfo`
  - `json`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `pdo_mysql`
  - `tokenizer`
  - `xml`
- Composer dəstəyi (SSH və ya local build + upload)

## 2. Qovluq Quruluşu

Tövsiyə olunan quruluş:

- `/home/<user>/eco-dc-app` → Laravel project root
- `/home/<user>/public_html` → yalnız `eco-dc-app/public` content

Diqqət: `.env`, `vendor`, `storage` web root daxilində olmamalıdır.

## 3. İlkin Qurulum

Layihə qovluğunda:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.example .env
php artisan key:generate
```

Alternativ: avtomatlaşdırılmış deploy script:

```bash
./scripts/deploy_shared.sh
```

## 4. `.env` Production Nümunəsi

```env
APP_NAME="ECO DC"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.az

APP_LOCALE=az
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eco_dc
DB_USERNAME=eco_dc_user
DB_PASSWORD=strong-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

ALLOWED_LOGIN_DOMAIN=company.az
ADMIN_EMAIL=admin@company.az
ADMIN_PASSWORD=StrongAdminPassword123!
```

## 5. DB və Seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

## 6. Cache Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Deploy sonrası yeniləmədə:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Deploy sonrası smoke test:

```bash
./scripts/smoke_test.sh https://your-domain.az
```

## 7. Cron və Queue

cPanel Cron (hər dəqiqə):

```bash
* * * * * /usr/bin/php /home/<user>/eco-dc-app/artisan schedule:run >> /dev/null 2>&1
```

Queue worker (əgər lazımdırsa):

```bash
/usr/bin/php /home/<user>/eco-dc-app/artisan queue:work --sleep=3 --tries=3
```

## 8. Fayl İcazələri

Yazma icazəsi lazımdır:

- `storage/`
- `bootstrap/cache/`

## 9. Təhlükəsizlik Checklist

- `APP_DEBUG=false`
- HTTPS məcburi
- Güclü DB və admin şifrələri
- `.env` web root xaricində
- Backup aktiv (DB + storage)
- `allowed_login_domain` düzgün konfiqurasiya olunub
- Register/Forgot Password route-ları mövcud deyil

## 10. Smoke Test

Deploy sonrası yoxlanmalıdır:

1. Login yalnız `@allowed_login_domain` email ilə işləyir.
2. Deaktiv user daxil ola bilmir.
3. Yeni sənəd yaradılanda prefiks `ECP-{KATEQORIYA}-{YYYY}-{0001}` formatında gəlir.
4. Attachment toggle off olduqda upload sahəsi görünmür və backend upload qəbul etmir.
5. Admin panel (`users/categories/folders/branding/audit-logs`) işləyir.
