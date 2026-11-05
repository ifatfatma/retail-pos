import { ref } from 'vue';

// Singleton state for notifications
const toasts = ref([]);
const confirmState = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'danger', // 'danger' | 'warning' | 'info' | 'success'
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    isAlertOnly: false,
    resolve: null,
});

let toastCounter = 0;

export function useNotification() {
    function showToast(message, type = 'success', title = '', duration = 3500) {
        const id = ++toastCounter;
        const defaultTitle = type === 'success' ? 'Success' : type === 'error' ? 'Notice' : type === 'warning' ? 'Attention' : 'Info';
        
        const toastItem = {
            id,
            type,
            title: title || defaultTitle,
            message: String(message || ''),
            duration,
            createdAt: Date.now(),
        };

        toasts.value.push(toastItem);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    }

    function removeToast(id) {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    }

    const toast = {
        success(message, title = 'Success', duration = 3500) {
            return showToast(message, 'success', title, duration);
        },
        error(message, title = 'Error', duration = 4500) {
            return showToast(message, 'error', title, duration);
        },
        warning(message, title = 'Warning', duration = 4000) {
            return showToast(message, 'warning', title, duration);
        },
        info(message, title = 'Info', duration = 3500) {
            return showToast(message, 'info', title, duration);
        },
    };

    // Themed confirmation dialog (Promise-based replacement for window.confirm)
    function confirmDialog({
        title = 'Are you sure?',
        message = 'This action cannot be undone.',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        type = 'danger',
    }) {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title,
                message,
                type,
                confirmText,
                cancelText,
                isAlertOnly: false,
                resolve,
            };
        });
    }

    // Themed alert dialog (Promise-based replacement for window.alert)
    function alertDialog({
        title = 'Notice',
        message = '',
        okText = 'Got It',
        type = 'info',
    }) {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title,
                message,
                type,
                confirmText: okText,
                cancelText: '',
                isAlertOnly: true,
                resolve,
            };
        });
    }

    function handleConfirmAction(result) {
        if (confirmState.value.resolve) {
            confirmState.value.resolve(result);
        }
        confirmState.value.isOpen = false;
        confirmState.value.resolve = null;
    }

    // Attach global helpers & gracefully upgrade window.alert
    if (typeof window !== 'undefined' && !window.__notification_installed__) {
        window.__notification_installed__ = true;
        window.$toast = toast;
        window.$confirm = confirmDialog;
        window.$alert = alertDialog;

        // Upgrade native window.alert to theme-matched toast notification
        window.alert = (msg) => {
            const str = String(msg || '');
            if (str.toLowerCase().includes('success') || str.toLowerCase().includes('added')) {
                toast.success(str, 'Success');
            } else if (str.toLowerCase().includes('error') || str.toLowerCase().includes('fail') || str.toLowerCase().includes('cannot')) {
                toast.error(str, 'Notice');
            } else if (str.toLowerCase().includes('warn') || str.toLowerCase().includes('only') || str.toLowerCase().includes('stock')) {
                toast.warning(str, 'Attention');
            } else {
                toast.info(str, 'Notification');
            }
        };
    }

    return {
        toasts,
        confirmState,
        toast,
        removeToast,
        confirmDialog,
        alertDialog,
        handleConfirmAction,
    };
}
