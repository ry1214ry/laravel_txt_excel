const initConverterPage = () => {
    const form = document.getElementById('upload-form');
    const fileInput = document.getElementById('txt_file');
    const dropZone = document.getElementById('drop-zone');
    const filePanel = document.getElementById('selected-file');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const submitButton = document.getElementById('submit-btn');
    const submitLabel = document.getElementById('submit-label');
    const progressBar = document.getElementById('progress-bar');

    if (!form || !fileInput || !dropZone) {
        return;
    }

    const formatBytes = (bytes) => {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '';
        }

        if (bytes < 1024) {
            return `${bytes} B`;
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const renderFile = (file) => {
        const hasFile = Boolean(file);

        dropZone.classList.toggle('has-file', hasFile);

        if (!filePanel || !fileName || !fileSize) {
            return;
        }

        filePanel.hidden = !hasFile;
        fileName.textContent = hasFile ? file.name : '';
        fileSize.textContent = hasFile ? formatBytes(file.size) : '';
    };

    const activateDropZone = (active) => {
        dropZone.classList.toggle('is-active', active);
    };

    fileInput.addEventListener('change', () => {
        renderFile(fileInput.files?.[0]);
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropZone.addEventListener(eventName, (event) => {
            event.preventDefault();
            activateDropZone(true);
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropZone.addEventListener(eventName, (event) => {
            event.preventDefault();
            activateDropZone(false);
        });
    });

    dropZone.addEventListener('drop', (event) => {
        const [file] = event.dataTransfer?.files ?? [];

        if (!file) {
            return;
        }

        fileInput.files = event.dataTransfer.files;
        renderFile(file);
    });

    form.addEventListener('submit', () => {
        if (!fileInput.files?.length) {
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        if (submitLabel) {
            submitLabel.textContent = 'Creating workbook...';
        }

        if (progressBar) {
            progressBar.hidden = false;
            progressBar.setAttribute('aria-hidden', 'false');
        }
    });

    renderFile(fileInput.files?.[0]);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConverterPage, { once: true });
} else {
    initConverterPage();
}
