const initConfirmForms = () => {
    document.addEventListener('submit', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        const message = target.dataset.confirm;
        if (!message) {
            return;
        }

        if (!window.confirm(message)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
};

const initCopyActions = () => {
    document.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const button = target.closest('[data-copy-target]');
        if (!(button instanceof HTMLElement)) {
            return;
        }

        const selector = button.dataset.copyTarget;
        if (!selector) {
            return;
        }

        const source = document.querySelector(selector);
        if (!(source instanceof HTMLElement)) {
            return;
        }

        const text = source.textContent?.trim() ?? '';
        if (!text || text === '-') {
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
        } catch (_error) {
            // Fail silently; copy is an enhancement.
        }
    });
};

const bindDropzone = (dropzone) => {
    if (!(dropzone instanceof HTMLElement)) {
        return;
    }

    const input = dropzone.querySelector('input[type="file"]');
    const label = dropzone.querySelector('[data-dropzone-label]');
    if (!(input instanceof HTMLInputElement) || !(label instanceof HTMLElement)) {
        return;
    }

    const defaultLabel = label.textContent?.trim() ?? '';
    const syncLabel = () => {
        const fileName = input.files?.[0]?.name;
        label.textContent = fileName || defaultLabel;
    };

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.add('version-dropzone-active');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('version-dropzone-active');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) {
            return;
        }

        input.files = files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    dropzone.addEventListener('click', () => input.click());
    input.addEventListener('change', syncLabel);
    syncLabel();
};

const initDropzones = () => {
    document.querySelectorAll('[data-dropzone]').forEach(bindDropzone);
};

const initPrefixPreview = () => {
    const form = document.querySelector('form[data-prefix-endpoint]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const categorySelect = form.querySelector('#category_id');
    const folderSelect = form.querySelector('#folder_id');
    const prefixPreview = document.getElementById('prefix-preview');
    const endpoint = form.dataset.prefixEndpoint;

    if (
        !(categorySelect instanceof HTMLSelectElement) ||
        !(folderSelect instanceof HTMLSelectElement) ||
        !(prefixPreview instanceof HTMLElement) ||
        !endpoint
    ) {
        return;
    }

    const loadingText = prefixPreview.dataset.loadingText?.trim() || '...';
    const renderEmpty = () => {
        prefixPreview.textContent = '-';
    };

    const updatePreview = async () => {
        const categoryId = categorySelect.value;
        const folderId = folderSelect.value;

        if (!categoryId || !folderId) {
            renderEmpty();
            return;
        }

        prefixPreview.textContent = loadingText;

        try {
            const params = new URLSearchParams({
                category_id: categoryId,
                folder_id: folderId,
            });

            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Preview request failed');
            }

            const payload = await response.json();
            prefixPreview.textContent = payload.prefix_code ?? '-';
        } catch (_error) {
            renderEmpty();
        }
    };

    categorySelect.addEventListener('change', updatePreview);
    folderSelect.addEventListener('change', updatePreview);
    updatePreview();
};

const initLocaleDropdowns = () => {
    const dropdowns = Array.from(document.querySelectorAll('[data-locale-dropdown]')).map((root) => {
        if (!(root instanceof HTMLElement)) {
            return null;
        }

        const trigger = root.querySelector('[data-locale-trigger]');
        const panel = root.querySelector('[data-locale-panel]');
        const chevron = root.querySelector('[data-locale-chevron]');

        if (!(trigger instanceof HTMLButtonElement) || !(panel instanceof HTMLElement)) {
            return null;
        }

        const forms = Array.from(root.querySelectorAll('form[data-locale-form]')).filter(
            (element) => element instanceof HTMLFormElement,
        );

        return {
            root,
            trigger,
            panel,
            chevron: chevron instanceof HTMLElement ? chevron : null,
            forms,
        };
    }).filter(Boolean);

    if (dropdowns.length === 0) {
        return;
    }

    const close = (dropdown) => {
        dropdown.panel.classList.add('hidden');
        dropdown.trigger.setAttribute('aria-expanded', 'false');
        dropdown.chevron?.classList.remove('rotate-180');
    };

    const open = (dropdown) => {
        dropdown.panel.classList.remove('hidden');
        dropdown.trigger.setAttribute('aria-expanded', 'true');
        dropdown.chevron?.classList.add('rotate-180');
    };

    const closeAll = (except = null) => {
        dropdowns.forEach((dropdown) => {
            if (except && dropdown.root === except.root) {
                return;
            }

            close(dropdown);
        });
    };

    dropdowns.forEach((dropdown) => {
        // Ensure deterministic initial state after page restores from bfcache.
        close(dropdown);

        dropdown.trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdown.trigger.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                close(dropdown);
                return;
            }

            closeAll(dropdown);
            open(dropdown);
        });

        dropdown.panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        dropdown.forms.forEach((form) => {
            form.addEventListener('submit', () => {
                close(dropdown);
            });
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) {
            closeAll();
            return;
        }

        const clickedInsideDropdown = dropdowns.some((dropdown) => dropdown.root.contains(target));
        if (!clickedInsideDropdown) {
            closeAll();
        }
    });

    document.addEventListener('focusin', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) {
            closeAll();
            return;
        }

        const focusedInsideDropdown = dropdowns.some((dropdown) => dropdown.root.contains(target));
        if (!focusedInsideDropdown) {
            closeAll();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        closeAll();
    });
};

const boot = () => {
    initConfirmForms();
    initCopyActions();
    initDropzones();
    initPrefixPreview();
    initLocaleDropdowns();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
