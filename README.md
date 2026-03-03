# ECO DC - Şirkətdaxili Sənəd Dövriyyə Platforması

ECO DC bir şirkət üçün (single-company) nəzərdə tutulmuş sənəd idarəetmə və dövriyyə sistemidir.  
Sistem mobile-first, tam responsiv və shared hosting mühitinə uyğun qurulub.

## 1. Əsas Xüsusiyyətlər

- Login yalnız aktiv istifadəçilər üçün mümkündür.
- Login yalnız branding-də təyin edilən domen e-poçtu ilə mümkündür.
- Yeni sənəd yaratma, redaktə və silmə.
- Sənəd prefiks kodunun real-time önizlənməsi və copy imkanı.
- Sənəd versiyalaşdırması:
  - Fayl yükləmə (drag & drop daxil)
  - Versiya qeydi (açıqlama)
  - Yükləyən istifadəçi və tarix
  - Versiya silmə
- Sənəd siyahısında filter:
  - Sənəd adına görə
  - İstifadəçiyə görə
  - Kateqoriya və qovluğa görə
  - Tarix intervalına görə
- Pagination ilə standart siyahılama.
- Admin panel:
  - İstifadəçi idarəetməsi
  - Role/permission idarəetməsi
  - Kateqoriya idarəetməsi (çoxdilli ad)
  - Qovluq idarəetməsi (ana/alt, çoxdilli ad)
  - Branding idarəetməsi
  - Audit log və CSV export
- Logo/Favicon yükləmə:
  - SVG dəstəyi
  - Təhlükəsiz SVG yoxlaması
  - Logo varsa UI-da logo görünür, yoxdursa şirkət adı görünür.

## 2. Prefiks Standartı

Sənəd prefiksi bu formatla generasiya olunur:

`ECP-{QOVLUQ}/{KATEQORIYA}-{YYYY}/{0001}`

Nümunə:

`ECP-MAIN/HR-2026/0001`

## 3. Rollar və Giriş Modeli

- `admin`
  - Tam giriş
  - Branding, audit, user, kateqoriya, qovluq və icazə idarəetməsi
- `employee`
  - Verilən icazələrə uyğun sənəd əməliyyatları
  - Öz sənədlərini redaktə/silmə

Qeyd:

- Yeni qeydiyyat (register) yoxdur.
- “Şifrəni unutdum” (password reset via email) yoxdur.
- Yalnız admin tərəfindən user yaradılır və idarə olunur.

## 4. Təhlükəsizlik Yanaşması

Sistemdə aşağıdakı əsas təhlükəsizlik tədbirləri tətbiq edilib:

- Aktiv olmayan user daxil ola bilmir (`is_active` yoxlanışı).
- Login domen məhdudiyyəti (`allowed_login_domain`).
- Login üçün rate limit.
- Fayl versiyalarında checksum (`sha256`) yoxlanışı.
- Attachment toggle deaktivdirsə upload endpoint-ləri backend səviyyəsində bloklanır.
- Secure headers:
  - `Content-Security-Policy`
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
  - `Strict-Transport-Security` (HTTPS olduqda)
- Auth olunan səhifələr üçün `no-store` cache siyasəti.
- Audit log ilə kritik əməliyyatların izlənməsi.
- SVG upload üçün aktiv məzmun/script bloklaması.

## 5. Texnoloji Stack

- PHP `8.2+`
- Laravel `12`
- MySQL `8+` və ya SQLite (lokal üçün)
- Tailwind CSS
- Alpine.js
- Spatie:
  - `laravel-permission`
  - `laravel-activitylog`
- Vite

## 6. Lokal Qurulum

### 6.1 Tələblər

- PHP 8.2+
- Composer
- Node.js 20+ və npm
- MySQL və ya SQLite

### 6.2 Sürətli start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Sistem: `http://127.0.0.1:8000`

## 7. Vacib `.env` Parametrləri

```env
APP_NAME="ECO DC"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=az
APP_FALLBACK_LOCALE=en
APP_AVAILABLE_LOCALES=az,en
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eco_dc
DB_USERNAME=root
DB_PASSWORD=

ALLOWED_LOGIN_DOMAIN=company.az
ADMIN_EMAIL=admin@company.az
ADMIN_PASSWORD=Admin@12345
```

Qeyd:

- Shared hosting üçün `APP_ENV=production`, `APP_DEBUG=false` olmalıdır.
- Timezone branding paneldən dəyişdirilə bilər (məs: `Asia/Baku`).

## 8. Komandalar

```bash
# Dev
composer run dev

# Test
php artisan test

# Frontend build
npm run build

# Cache optimize (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 9. Shared Hosting Deploy

Shared hosting üçün ayrıca təlimat:

[`DEPLOY_SHARED_HOSTING.md`](./DEPLOY_SHARED_HOSTING.md)

Bu sənəddə var:

- Server tələbləri
- Qovluq strukturu
- Production `.env`
- Migrate/seed
- Cache optimize
- Cron/queue
- Təhlükəsizlik checklist

## 10. Çoxdillilik

UI əsasən Azərbaycan dilində hazırlanıb.  
Arxitektura genişlənəndir və yeni dillər əlavə etmək mümkündür:

- `APP_AVAILABLE_LOCALES=az,en,...`
- `lang/{locale}/ui.php` və `lang/{locale}/messages.php`
- Kateqoriya və qovluqlar üçün `name_translations` strukturu

## 11. Brending

Admin branding bölməsindən:

- Şirkət adı
- Giriş domeni
- Əsas/ikinci rəng
- Saat qurşağı
- Logo (SVG daxil)
- Favicon
- Fayl əlavəsi toggle

dəyişə bilər.

## 12. UI/UX Prinsipləri

- Mobile-first
- Mobile app style alt menyu
- Tam responsiv desktop/mobile layout
- Safari daxil cross-browser uyğunluğu üçün xüsusi fallback-lar
- İkon əsaslı action düymələri

## 13. Lisenziya

Bu layihə şirkətdaxili istifadə üçün hazırlanıb.  
Açıq mənbə istifadəsi üçün ayrıca hüquqi qərar tələb olunur.

