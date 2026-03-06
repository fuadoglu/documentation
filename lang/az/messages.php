<?php

return [
    'auth' => [
        'must_change_password' => 'Təhlükəsizlik üçün şifrənizi yeniləyin.',
        'domain_not_allowed' => 'Bu e-poçt domeni ilə giriş icazəsi yoxdur.',
        'invalid_credentials' => 'Daxil etdiyiniz məlumatlar yanlışdır.',
        'too_many_attempts' => 'Çox sayda uğursuz cəhd var. :seconds saniyə sonra yenidən cəhd edin.',
        'inactive_account' => 'Hesabınız aktiv deyil.',
    ],

    'validation' => [
        'profile_domain_mismatch' => 'E-poçt domeni branding ayarı ilə uyğun olmalıdır.',
        'file_upload_disabled' => 'Fayl əlavəsi hazırda deaktivdir.',
        'email_domain_mismatch' => 'E-poçt domeni branding ayarları ilə uyğun deyil.',
        'translation_required' => 'Ən azı bir dil üçün ad daxil edilməlidir.',
        'unsafe_svg' => 'SVG faylı təhlükəsiz deyil. Script və aktiv məzmunlara icazə verilmir.',
    ],

    'status' => [
        'document_created' => 'Sənəd uğurla yaradıldı.',
        'document_updated' => 'Sənəd yeniləndi.',
        'document_deleted' => 'Sənəd silindi.',
        'document_version_uploaded' => 'Yeni sənəd versiyası yükləndi.',
        'document_version_deleted' => 'Sənəd versiyası silindi.',
        'password_updated' => 'Şifrə yeniləndi.',
        'profile_updated' => 'Profil yeniləndi.',

        'category_created' => 'Kateqoriya yaradıldı.',
        'category_updated' => 'Kateqoriya yeniləndi.',
        'category_status_changed' => 'Kateqoriya statusu dəyişdirildi.',
        'category_deleted' => 'Kateqoriya silindi.',

        'folder_created' => 'Qovluq yaradıldı.',
        'folder_updated' => 'Qovluq yeniləndi.',
        'folder_status_changed' => 'Qovluq statusu dəyişdirildi.',
        'folder_deleted' => 'Qovluq silindi.',

        'branding_updated' => 'Brendinq ayarları yeniləndi.',

        'user_created' => 'İstifadəçi yaradıldı.',
        'user_updated' => 'İstifadəçi yeniləndi.',
        'user_status_changed' => 'İstifadəçi statusu dəyişdirildi.',
        'user_password_reset' => 'İstifadəçi şifrəsi yeniləndi.',
        'user_deleted' => 'İstifadəçi silindi.',

        'employee_permissions_updated' => 'İşçi rolu üçün icazələr yeniləndi.',
    ],

    'error' => [
        'cannot_deactivate_self' => 'Öz hesabınızı deaktiv edə bilməzsiniz.',
        'cannot_delete_self' => 'Öz hesabınızı silə bilməzsiniz.',
        'file_not_found' => 'Fayl tapılmadı.',
        'file_integrity_failed' => 'Fayl bütövlüyü yoxlamasından keçmədi.',
        'file_store_failed' => 'Fayl yaddaşa yazıla bilmədi.',
    ],

    'audit' => [
        'document_created' => 'Sənəd yaradıldı',
        'document_updated' => 'Sənəd yeniləndi',
        'document_deleted' => 'Sənəd silindi',
        'document_version_uploaded' => 'Sənəd versiyası yükləndi',
        'document_version_deleted' => 'Sənəd versiyası silindi',
        'document_attachment_downloaded' => 'Sənəd əlavəsi yükləndi',

        'category_created' => 'Kateqoriya yaradıldı',
        'category_updated' => 'Kateqoriya yeniləndi',
        'category_status_changed' => 'Kateqoriya statusu dəyişdirildi',
        'category_deleted' => 'Kateqoriya silindi',

        'folder_created' => 'Qovluq yaradıldı',
        'folder_updated' => 'Qovluq yeniləndi',
        'folder_status_changed' => 'Qovluq statusu dəyişdirildi',
        'folder_deleted' => 'Qovluq silindi',

        'user_created' => 'İstifadəçi yaradıldı',
        'user_updated' => 'İstifadəçi yeniləndi',
        'user_status_changed' => 'İstifadəçi statusu dəyişdirildi',
        'user_password_reset' => 'İstifadəçi şifrəsi yeniləndi',
        'user_deleted' => 'İstifadəçi silindi',

        'branding_updated' => 'Brendinq ayarları yeniləndi',
        'employee_permissions_updated' => 'İşçi rolunun icazələri yeniləndi',
        'setup_completed' => 'İlkin quraşdırma tamamlandı',
    ],
];
