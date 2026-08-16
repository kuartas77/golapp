import { computed, onBeforeUnmount, ref, watch } from 'vue';

export const useGuardianEmailVerification = ({ api, endpoints, values, normalizeEmail }) => {
    const emailVerificationLoading = ref(false);
    const emailCodeRequested = ref(false);
    const emailVerified = ref(false);
    const emailVerificationCode = ref('');
    const emailVerificationToken = ref('');
    const emailVerificationError = ref('');
    const verifiedEmailContext = ref('');
    const resendSeconds = ref(0);

    let resendInterval = null;

    const currentEmailContext = () => (
        `${String(values.tutor_num_doc ?? '').trim()}|${normalizeEmail(values.tutor_email)}`
    );

    const resendButtonLabel = computed(() => resendSeconds.value > 0
        ? `Reenviar en ${resendSeconds.value}s`
        : 'Reenviar código');

    const stopResendCountdown = () => {
        if (resendInterval) {
            window.clearInterval(resendInterval);
            resendInterval = null;
        }
    };

    const startResendCountdown = () => {
        stopResendCountdown();
        resendSeconds.value = 60;
        resendInterval = window.setInterval(() => {
            resendSeconds.value -= 1;
            if (resendSeconds.value <= 0) {
                stopResendCountdown();
            }
        }, 1000);
    };

    const clearEmailVerification = () => {
        emailCodeRequested.value = false;
        emailVerified.value = false;
        emailVerificationCode.value = '';
        emailVerificationToken.value = '';
        emailVerificationError.value = '';
        verifiedEmailContext.value = '';
        resendSeconds.value = 0;
        stopResendCountdown();
    };

    const requestGuardianEmailCode = async () => {
        if (!String(values.tutor_num_doc ?? '').trim() || !normalizeEmail(values.tutor_email)) {
            emailVerificationError.value = 'Ingresa el documento y el correo del acudiente antes de solicitar el código.';
            return;
        }

        emailVerificationLoading.value = true;
        emailVerificationError.value = '';

        try {
            const response = await api.post(endpoints.guardianEmailVerificationRequest, {
                tutor_num_doc: String(values.tutor_num_doc).trim(),
                tutor_email: normalizeEmail(values.tutor_email),
            });

            verifiedEmailContext.value = currentEmailContext();
            emailCodeRequested.value = true;
            emailVerificationCode.value = '';
            startResendCountdown();
        } catch (error) {
            emailVerificationError.value = error.response?.data?.errors?.tutor_email?.[0]
                || error.response?.data?.message
                || 'No pudimos enviar el código. Inténtalo nuevamente.';
        } finally {
            emailVerificationLoading.value = false;
        }
    };

    const confirmGuardianEmailCode = async () => {
        emailVerificationLoading.value = true;
        emailVerificationError.value = '';

        try {
            const response = await api.post(endpoints.guardianEmailVerificationConfirm, {
                tutor_num_doc: String(values.tutor_num_doc).trim(),
                tutor_email: normalizeEmail(values.tutor_email),
                verification_code: emailVerificationCode.value,
            });

            emailVerificationToken.value = response.data?.token || '';
            emailVerified.value = true;
            verifiedEmailContext.value = currentEmailContext();
            stopResendCountdown();
        } catch (error) {
            emailVerificationError.value = error.response?.data?.errors?.verification_code?.[0]
                || error.response?.data?.message
                || 'No pudimos verificar el código. Inténtalo nuevamente.';
        } finally {
            emailVerificationLoading.value = false;
        }
    };

    watch(
        () => [String(values.tutor_num_doc ?? '').trim(), normalizeEmail(values.tutor_email)],
        () => {
            if (verifiedEmailContext.value && verifiedEmailContext.value !== currentEmailContext()) {
                clearEmailVerification();
            }
        }
    );

    onBeforeUnmount(stopResendCountdown);

    return {
        clearEmailVerification,
        confirmGuardianEmailCode,
        emailCodeRequested,
        emailVerificationCode,
        emailVerificationError,
        emailVerificationLoading,
        emailVerificationToken,
        emailVerified,
        requestGuardianEmailCode,
        resendButtonLabel,
        resendSeconds,
    };
};
