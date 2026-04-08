import { Tooltip } from 'bootstrap';

export default {
    mounted(el, binding) {
        new Tooltip(el, {
            title: binding.value,
            placement: binding.arg || 'top',
            trigger: 'hover focus', // Default trigger
            // Add other Bootstrap tooltip options as needed
        });
    },
    updated(el, binding) {
        const tooltipInstance = Tooltip.getInstance(el);
        if (tooltipInstance) {
            tooltipInstance.dispose(); // Dispose old instance
        }
        new Tooltip(el, {
            title: binding.value,
            placement: binding.arg || 'top',
            trigger: 'hover focus',
        });
    },
    unmounted(el) {
        const tooltipInstance = Tooltip.getInstance(el);
        if (tooltipInstance) {
            tooltipInstance.dispose();
        }
    },
};