<?php

return [
    'auth' => [
        'must_change_password' => 'Update your password for security.',
        'domain_not_allowed' => 'This email domain is not allowed.',
        'invalid_credentials' => 'The provided credentials are invalid.',
        'too_many_attempts' => 'Too many failed attempts. Try again in :seconds seconds.',
        'inactive_account' => 'Your account is not active.',
    ],

    'validation' => [
        'profile_domain_mismatch' => 'Email domain must match branding settings.',
        'file_upload_disabled' => 'File upload is currently disabled.',
        'email_domain_mismatch' => 'Email domain does not match branding settings.',
        'translation_required' => 'At least one translated name is required.',
        'unsafe_svg' => 'The SVG file is not safe. Scripted or active content is not allowed.',
    ],

    'status' => [
        'document_created' => 'Document created successfully.',
        'document_updated' => 'Document updated.',
        'document_deleted' => 'Document deleted.',
        'document_version_uploaded' => 'New document version uploaded.',
        'document_version_deleted' => 'Document version deleted.',
        'password_updated' => 'Password updated.',
        'profile_updated' => 'Profile updated.',

        'category_created' => 'Category created.',
        'category_updated' => 'Category updated.',
        'category_status_changed' => 'Category status changed.',
        'category_deleted' => 'Category deleted.',

        'folder_created' => 'Folder created.',
        'folder_updated' => 'Folder updated.',
        'folder_status_changed' => 'Folder status changed.',
        'folder_deleted' => 'Folder deleted.',

        'branding_updated' => 'Branding settings updated.',

        'user_created' => 'User created.',
        'user_updated' => 'User updated.',
        'user_status_changed' => 'User status changed.',
        'user_password_reset' => 'User password updated.',
        'user_deleted' => 'User deleted.',

        'employee_permissions_updated' => 'Employee role permissions updated.',
    ],

    'error' => [
        'cannot_deactivate_self' => 'You cannot deactivate your own account.',
        'cannot_delete_self' => 'You cannot delete your own account.',
        'file_not_found' => 'File not found.',
        'file_integrity_failed' => 'File integrity verification failed.',
        'file_store_failed' => 'The file could not be stored.',
    ],

    'audit' => [
        'document_created' => 'Document created',
        'document_updated' => 'Document updated',
        'document_deleted' => 'Document deleted',
        'document_version_uploaded' => 'Document version uploaded',
        'document_version_deleted' => 'Document version deleted',
        'document_attachment_downloaded' => 'Document attachment downloaded',

        'category_created' => 'Category created',
        'category_updated' => 'Category updated',
        'category_status_changed' => 'Category status changed',
        'category_deleted' => 'Category deleted',

        'folder_created' => 'Folder created',
        'folder_updated' => 'Folder updated',
        'folder_status_changed' => 'Folder status changed',
        'folder_deleted' => 'Folder deleted',

        'user_created' => 'User created',
        'user_updated' => 'User updated',
        'user_status_changed' => 'User status changed',
        'user_password_reset' => 'User password reset',
        'user_deleted' => 'User deleted',

        'branding_updated' => 'Branding settings updated',
        'employee_permissions_updated' => 'Employee role permissions updated',
        'setup_completed' => 'Initial setup completed',
    ],
];
