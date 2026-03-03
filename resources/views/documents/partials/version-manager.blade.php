@if ($attachmentsEnabled)
    <section class="app-card">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('ui.documents.versions_title') }}</h3>

        @if ($canManage)
            <form method="POST" action="{{ route('documents.versions.store', $document) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-2 md:auto-rows-fr">
                @csrf
                <div class="flex h-full flex-col">
                    <label class="app-label" for="version_file">{{ __('ui.documents.version_file') }}</label>
                    <div data-dropzone class="version-dropzone">
                        <input id="version_file" name="file" type="file" required class="sr-only">
                        <div class="pointer-events-none flex w-full flex-col items-center justify-center gap-1 text-center">
                            <x-icon name="attachments" class="h-7 w-7 text-teal-700" />
                            <p data-dropzone-label class="text-sm font-semibold text-slate-900">{{ __('ui.documents.version_drop_title') }}</p>
                            <p class="text-xs text-slate-500">{{ __('ui.documents.version_drop_hint') }}</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex h-full flex-col">
                    <label class="app-label" for="version_note">{{ __('ui.documents.version_note') }}</label>
                    <textarea id="version_note" name="version_note" rows="3" class="app-input min-h-[132px] flex-1" maxlength="500" required placeholder="{{ __('ui.documents.version_note_placeholder') }}">{{ old('version_note') }}</textarea>
                    @error('version_note')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <button class="app-button-primary w-full sm:w-auto">{{ __('ui.documents.version_upload_submit') }}</button>
                </div>
            </form>
        @endif

        <div class="mt-4 space-y-2">
            @forelse ($document->attachments as $attachment)
                <div class="rounded-xl border border-slate-200 p-3">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-teal-700">{{ __('ui.documents.version') }} {{ $attachment->version_number }}</p>
                            <p class="mt-1 break-words text-sm font-medium text-slate-900">{{ $attachment->original_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ number_format($attachment->file_size / 1024, 2) }} {{ __('ui.common.file_size_kb') }}</p>
                        </div>
                        <div class="app-action-group w-full sm:w-auto sm:justify-end">
                            <a href="{{ route('documents.attachments.download', [$document, $attachment]) }}" class="app-button-secondary w-full sm:w-auto"><x-icon name="attachments" class="h-5 w-5" /><span>{{ __('ui.common.download') }}</span></a>
                            @if ($canManage)
                                <form method="POST" action="{{ route('documents.versions.destroy', [$document, $attachment]) }}" data-confirm="{{ __('ui.documents.confirm_version_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="app-button-danger w-full sm:w-auto">
                                        <x-icon name="delete" class="h-5 w-5" />
                                        <span>{{ __('ui.common.delete') }}</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-600">
                        <p><span class="font-semibold">{{ __('ui.documents.version_note') }}:</span> {{ $attachment->version_note ?? '-' }}</p>
                        <p class="mt-1"><span class="font-semibold">{{ __('ui.documents.version_date') }}:</span> {{ $attachment->created_at?->format('d.m.Y H:i') }}</p>
                        <p class="mt-1"><span class="font-semibold">{{ __('ui.documents.version_uploaded_by') }}:</span> {{ $attachment->uploader?->name ?? '-' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('ui.documents.no_versions') }}</p>
            @endforelse
        </div>
    </section>
@endif
