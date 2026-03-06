<?php

return [
    'enabled' => (bool) env('INSTALLER_ENABLED', true),
    'lock_file' => env('INSTALLER_LOCK_FILE', 'app/installed.lock'),
    'self_destruct' => (bool) env('INSTALLER_SELF_DESTRUCT', true),
    'enforce_during_tests' => (bool) env('INSTALLER_ENFORCE_DURING_TESTS', false),
];
