import Swal from 'sweetalert2';

/**
 * Modern SweetAlert2 replacement for native browser confirm() dialog.
 * @param {string|Object} messageOrOptions
 * @param {Object} [options]
 * @returns {Promise<boolean>}
 */
export async function confirmDialog(messageOrOptions, options = {}) {
    let opts = {};
    if (typeof messageOrOptions === 'string') {
        opts = { text: messageOrOptions, ...options };
    } else if (typeof messageOrOptions === 'object' && messageOrOptions !== null) {
        opts = { ...messageOrOptions, ...options };
    }

    const text = opts.text || '';
    const isDelete = /hapus|delete|clear|reset|lepas|fatal/i.test(text + ' ' + (opts.title || ''));

    const result = await Swal.fire({
        title: opts.title || 'Konfirmasi',
        text: opts.html ? undefined : text,
        html: opts.html || undefined,
        icon: opts.icon || (isDelete ? 'warning' : 'question'),
        showCancelButton: true,
        confirmButtonColor: opts.confirmButtonColor || (isDelete ? '#dc2626' : '#2563eb'),
        cancelButtonColor: opts.cancelButtonColor || '#64748b',
        confirmButtonText: opts.confirmButtonText || (isDelete ? 'Ya, Lanjutkan' : 'Ya, Setuju'),
        cancelButtonText: opts.cancelButtonText || 'Batal',
        reverseButtons: true,
        focusCancel: true,
        ...opts,
    });

    return result.isConfirmed;
}

// Make globally accessible in browser runtime
if (typeof window !== 'undefined') {
    window.confirmDialog = confirmDialog;
}

export default confirmDialog;
