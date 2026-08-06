import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { FILE_FIELDS, SIGNATURE_FIELDS } from '@/validation/portal/inscriptionSchema';

export const useInscriptionPersistence = ({
    storageKey,
    values,
    defaultValues,
    resetForm,
    currentStep,
    globalError,
    clearEmailVerification,
}) => {
    const persistencePaused = ref(false);
    let persistTimeout = null;

    const readPersistedValues = () => {
        try {
            const serialized = localStorage.getItem(storageKey);
            return serialized ? JSON.parse(serialized) : null;
        } catch (error) {
            return null;
        }
    };

    const clearPersistedValues = () => {
        localStorage.removeItem(storageKey);
    };

    const sanitizeValuesForStorage = (currentValues) => Object.fromEntries(
        Object.entries(currentValues)
            .filter(([key]) => !FILE_FIELDS.includes(key) && !SIGNATURE_FIELDS.includes(key))
            .map(([key, value]) => [key, value ?? ''])
    );

    const pausePersistence = async (callback) => {
        persistencePaused.value = true;
        await callback();
        await nextTick();
        persistencePaused.value = false;
    };

    const restorePersistedValues = async () => {
        const persistedValues = readPersistedValues();

        if (!persistedValues) {
            return;
        }

        await pausePersistence(async () => {
            resetForm({
                values: {
                    ...defaultValues(),
                    ...persistedValues,
                },
            });
        });
    };

    const resetWizard = async () => {
        await pausePersistence(async () => {
            clearPersistedValues();
            resetForm({
                values: defaultValues(),
            });
            currentStep.value = 0;
            globalError.value = '';
            clearEmailVerification();
        });
    };

    watch(
        values,
        (currentValues) => {
            if (persistencePaused.value) {
                return;
            }

            clearTimeout(persistTimeout);

            persistTimeout = window.setTimeout(() => {
                localStorage.setItem(storageKey, JSON.stringify(sanitizeValuesForStorage(currentValues)));
            }, 250);
        },
        { deep: true }
    );

    onBeforeUnmount(() => {
        clearTimeout(persistTimeout);
    });

    return {
        resetWizard,
        restorePersistedValues,
    };
};
