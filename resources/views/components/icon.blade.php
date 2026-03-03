@props([
    'name',
    'class' => 'h-5 w-5',
])

@switch($name)
    @case('dashboard')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h7.5v7.5h-7.5zm9 0h7.5v4.5h-7.5zm0 6h7.5v10.5h-7.5zm-9 3h7.5v7.5h-7.5z" />
        </svg>
        @break

    @case('home')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10.5 9-7.5 9 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 9.75V19.5h13.5V9.75" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5V15h3v4.5" />
        </svg>
        @break

    @case('home-main')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 10.5 12 3l9.75 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.9v8.85A1.5 1.5 0 0 0 6 20.25h4.5V14.7h3v5.55H18a1.5 1.5 0 0 0 1.5-1.5V9.9" />
        </svg>
        @break

    @case('documents')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.625a1.125 1.125 0 0 0-1.125-1.125h-9.75a1.125 1.125 0 0 0-1.125 1.125v12.75A1.125 1.125 0 0 0 8.625 19.5h4.125m6.75-1.5-3-3m0 0-3 3m3-3V21" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.25h7.5M9.75 11.25h7.5" />
        </svg>
        @break

    @case('create')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        @break

    @case('profile')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.964 0a9 9 0 1 0-11.964 0m11.964 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        @break

    @case('admin')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M3.75 8.25A2.25 2.25 0 0 1 6 6h12a2.25 2.25 0 0 1 2.25 2.25v3.214a9 9 0 1 1-16.5 0V8.25Z" />
        </svg>
        @break

    @case('users')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a8.97 8.97 0 0 0 3.741-1.245m-3.741 1.245A9.006 9.006 0 0 1 12 21a9.006 9.006 0 0 1-6-2.28m12 0a5.97 5.97 0 0 0-.94-3.026m-10.12 3.026a5.97 5.97 0 0 1 .94-3.026m9.18-4.674a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm6 3a2.25 2.25 0 1 0-4.5 0 2.25 2.25 0 0 0 4.5 0Zm-13.5 0a2.25 2.25 0 1 0-4.5 0 2.25 2.25 0 0 0 4.5 0Z" />
        </svg>
        @break

    @case('permissions')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 5.25 3.75 3.75m-3.75-3.75a2.25 2.25 0 1 0-3.182 3.182m3.182-3.182L8.47 12.53a4.5 4.5 0 0 0-1.126 1.948L6.3 18.6l4.122-1.044a4.5 4.5 0 0 0 1.948-1.126l7.28-7.28m-7.28 7.28L3.75 20.25" />
        </svg>
        @break

    @case('categories')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75V6A2.25 2.25 0 0 0 14.25 3.75H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h.75m9.75-9.75h1.5A2.25 2.25 0 0 1 20.25 9v9a2.25 2.25 0 0 1-2.25 2.25h-9A2.25 2.25 0 0 1 6.75 18v-1.5" />
        </svg>
        @break

    @case('folders')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 0 1 4.5 6h3.879a1.5 1.5 0 0 1 1.06.44l1.06 1.06a1.5 1.5 0 0 0 1.06.44H19.5A1.5 1.5 0 0 1 21 9.44v7.06A1.5 1.5 0 0 1 19.5 18h-15A1.5 1.5 0 0 1 3 16.5v-9Z" />
        </svg>
        @break

    @case('branding')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 5.47a.75.75 0 0 1 1.06 0l7.94 7.94a.75.75 0 0 1 0 1.06l-3.47 3.47a.75.75 0 0 1-1.06 0L6.06 10a.75.75 0 0 1 0-1.06l3.47-3.47Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m13.5 15-6 6m0 0-1.5-1.5m1.5 1.5 1.5-1.5" />
        </svg>
        @break

    @case('audit')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 3.75H8.25A2.25 2.25 0 0 0 6 6v12a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 18V6a2.25 2.25 0 0 0-2.25-2.25Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h6m-6 3h6m-6 3h3" />
        </svg>
        @break

    @case('logout')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-3-3h9m0 0-3-3m3 3-3 3" />
        </svg>
        @break

    @case('open')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" />
        </svg>
        @break

    @case('edit')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651m-3.31-1.992-9.193 9.193a4.5 4.5 0 0 0-1.126 1.948L4.5 20.25l3.963-1.384a4.5 4.5 0 0 0 1.948-1.126l9.193-9.193a2.25 2.25 0 1 0-3.182-3.182Z" />
        </svg>
        @break

    @case('delete')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.088-2.201a51.964 51.964 0 0 0-3.324 0C9.16 2.313 8.25 3.296 8.25 4.477v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
        </svg>
        @break

    @case('copy')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 18h-7.5A2.25 2.25 0 0 1 6 15.75v-9A2.25 2.25 0 0 1 8.25 4.5h1.086a.75.75 0 0 0 .53-.22l.878-.88A.75.75 0 0 1 11.28 3h1.44a.75.75 0 0 1 .53.22l.878.88a.75.75 0 0 0 .53.22h1.086A2.25 2.25 0 0 1 18 6.75v9A2.25 2.25 0 0 1 15.75 18Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 9.75h3m-3 3h3" />
        </svg>
        @break

    @case('total-documents')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.75m0 0-3-3m3 3 3-3M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v7.5A2.25 2.25 0 0 1 16.5 15.75h-9A2.25 2.25 0 0 1 5.25 13.5V6A2.25 2.25 0 0 1 7.5 3.75Z" />
        </svg>
        @break

    @case('files-stack')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 6.75A2.25 2.25 0 0 1 9.75 4.5h7.5a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 17.25 18h-7.5a2.25 2.25 0 0 1-2.25-2.25v-9Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75V8.25A2.25 2.25 0 0 1 6.75 6h7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 8.25h6m-6 3h6m-6 3h3.75" />
        </svg>
        @break

    @case('today')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v10.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V7.5a2.25 2.25 0 0 1 2.25-2.25Z" />
        </svg>
        @break

    @case('attachments')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-6.315 6.315a4.5 4.5 0 1 1-6.364-6.364l8.486-8.486a3 3 0 1 1 4.243 4.243L9.939 16.94a1.5 1.5 0 1 1-2.121-2.121l7.425-7.425" />
        </svg>
        @break

    @case('quick-access')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.049 2.927-1.854 5.712a1.125 1.125 0 0 1-1.07.777H2.25l4.755 3.454a1.125 1.125 0 0 1 .408 1.257l-1.854 5.712 4.755-3.454a1.125 1.125 0 0 1 1.322 0l4.755 3.454-1.854-5.712a1.125 1.125 0 0 1 .408-1.257l4.755-3.454h-5.875a1.125 1.125 0 0 1-1.07-.777L11.049 2.927Z" />
        </svg>
        @break

    @case('flag-az')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" class="{{ $class }}" aria-hidden="true">
            <rect width="24" height="16" fill="#3f9cde" />
            <rect y="5.33" width="24" height="5.34" fill="#ed2939" />
            <rect y="10.67" width="24" height="5.33" fill="#00a651" />
            <circle cx="10.8" cy="8" r="2.5" fill="#fff" />
            <circle cx="11.6" cy="8" r="2.05" fill="#ed2939" />
            <path d="m14.25 6.55.33 1.02h1.07l-.87.63.33 1.02-.86-.63-.87.63.34-1.02-.87-.63h1.07z" fill="#fff" />
        </svg>
        @break

    @case('flag-en')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" class="{{ $class }}" aria-hidden="true">
            <rect width="24" height="16" fill="#012169" />
            <path d="M0 0l24 16M24 0 0 16" stroke="#fff" stroke-width="3.2" />
            <path d="M0 0l24 16M24 0 0 16" stroke="#c8102e" stroke-width="1.6" />
            <path d="M12 0v16M0 8h24" stroke="#fff" stroke-width="5" />
            <path d="M12 0v16M0 8h24" stroke="#c8102e" stroke-width="3" />
        </svg>
        @break

    @case('chevron-down')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
        </svg>
        @break

    @case('chevron-left')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 6-6 6 6 6" />
        </svg>
        @break

    @case('chevron-right')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
        </svg>
        @break

    @case('arrow-right')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="{{ $class }}" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 12h13.5m0 0-5.25-5.25M18.75 12l-5.25 5.25" />
        </svg>
        @break
@endswitch
