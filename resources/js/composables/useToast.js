import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
    const addToast = (message, type = 'info', duration = 4000) => {
        console.log('Adding toast:', message, type);
        const id = Date.now() + Math.random();

        toasts.value.push({
            id,
            message,
            type,
            duration
        });

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (message, duration) => addToast(message, 'success', duration);
    const error = (message, duration) => addToast(message, 'error', duration);
    const info = (message, duration) => addToast(message, 'info', duration);
    const warning = (message, duration) => addToast(message, 'warning', duration);
    // Specific requests from user
    const deleted = (message = 'Item deleted successfully', duration) => addToast(message, 'delete', duration);
    const updated = (message = 'Item updated successfully', duration) => addToast(message, 'updated', duration);

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        info,
        warning,
        deleted,
        updated
    };
}
