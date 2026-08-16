import { nextTick } from 'vue';
import { FILE_FIELDS } from '@/validation/portal/inscriptionSchema';

const INSCRIPTION_SUBMISSION_TIMEOUT_MS = 180000;

export const useInscriptionSubmission = ({
    api,
    endpoints,
    school,
    year,
    recaptcha,
    appConfig,
    recaptchaClient,
    checkboxFields,
    guardianEmailVerificationToken,
    clearGuardianEmailVerification,
    handleSubmit,
    steps,
    currentStep,
    setErrors,
    modalRef,
    resetWizard,
    normalizeEmail,
    appName,
    submitting,
    globalError,
}) => {
    const executeRecaptcha = async () => {
        if (!recaptcha?.enabled) {
            return '';
        }

        if (!appConfig.recaptchaSiteKey) {
            return null;
        }

        if (!recaptchaClient?.executeRecaptcha || !recaptchaClient?.recaptchaLoaded) {
            throw new Error('No pudimos validar el captcha. Recarga la página e inténtalo nuevamente.');
        }

        await recaptchaClient.recaptchaLoaded();

        return recaptchaClient.executeRecaptcha(recaptcha.action || 'inscriptions');
    };

    const buildFormData = (payload, recaptchaToken) => {
        const formData = new FormData();

        Object.entries(payload).forEach(([key, value]) => {
            if (FILE_FIELDS.includes(key)) {
                if (value instanceof File) {
                    formData.append(key, value);
                }
                return;
            }

            if (checkboxFields.value.includes(key)) {
                if (value) {
                    formData.append(key, '1');
                }
                return;
            }

            if (value === null || value === undefined) {
                return;
            }

            formData.append(key, typeof value === 'string' ? value : String(value));
        });

        if (recaptchaToken) {
            formData.append('g-recaptcha-response', recaptchaToken);
        }

        return formData;
    };

    const reportSubmissionError = async (error, submittedValues, elapsedMs) => {
        if (error.response) {
            return;
        }

        const fileSizes = Object.fromEntries(
            FILE_FIELDS.flatMap((field) => {
                const value = submittedValues[field];
                return value instanceof File ? [[field, value.size]] : [];
            })
        );

        try {
            await api.post(endpoints.clientError || '/api/v2/portal/inscription-client-errors', {
                school_slug: school.slug,
                endpoint: endpoints.store,
                error_code: error.code || null,
                error_message: error.message || null,
                status: error.response?.status || null,
                online: navigator.onLine,
                file_sizes: fileSizes,
                total_file_bytes: Object.values(fileSizes).reduce((total, size) => total + size, 0),
                timeout_ms: INSCRIPTION_SUBMISSION_TIMEOUT_MS,
                elapsed_ms: elapsedMs,
                client_timed_out: error.code === 'ECONNABORTED'
                    && elapsedMs >= INSCRIPTION_SUBMISSION_TIMEOUT_MS,
            });
        } catch (reportError) {
            // The original submission error remains the one shown to the user.
        }
    };

    const hideModal = () => {
        const modalElement = modalRef.value;

        if (!modalElement) {
            return;
        }

        const modalInstance = window.bootstrap?.Modal?.getInstance(modalElement)
            ?? window.bootstrap?.Modal?.getOrCreateInstance?.(modalElement);

        if (modalInstance) {
            modalInstance.hide();
            return;
        }

        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach((element) => element.remove());
    };

    const submitForm = handleSubmit(async (submittedValues) => {
        submitting.value = true;
        globalError.value = '';
        const submissionStartedAt = Date.now();

        try {
            const recaptchaToken = await executeRecaptcha();
            const payload = {
                ...submittedValues,
                email: normalizeEmail(submittedValues.email),
                tutor_email: normalizeEmail(submittedValues.tutor_email),
                guardian_email_verification_token: guardianEmailVerificationToken.value,
                year: String(submittedValues.year ?? year),
            };

            const formData = buildFormData(payload, recaptchaToken);

            await api.post(endpoints.store, formData, {
                timeout: INSCRIPTION_SUBMISSION_TIMEOUT_MS,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            await resetWizard();
            hideModal();

            await window.Swal.fire({
                icon: 'success',
                title: appName,
                text: 'Se ha creado la inscripción correctamente, se enviará al correo de notificación la información necesaria.',
            });

            window.location.reload();
        } catch (error) {
            await reportSubmissionError(error, submittedValues, Date.now() - submissionStartedAt);

            const response = error.response;
            const backendErrors = response?.data?.errors ?? {};

            if (backendErrors.guardian_email_verification_token || Number(response?.status) >= 500) {
                clearGuardianEmailVerification();
            }

            const fieldErrors = Object.fromEntries(
                Object.entries(backendErrors).map(([key, value]) => [
                    key,
                    Array.isArray(value) ? value[0] : value,
                ])
            );

            if (Object.keys(fieldErrors).length > 0) {
                const firstInvalidStep = steps.value.findIndex((step) =>
                    step.fields.some((field) => Boolean(fieldErrors[field]))
                );

                if (firstInvalidStep >= 0) {
                    currentStep.value = firstInvalidStep;
                    await nextTick();
                }

                setErrors(fieldErrors);
            }

            globalError.value = response?.data?.message
                || error.message
                || 'Algo salió mal, no hemos podido procesar la información en este momento.';

            await window.Swal.fire({
                icon: 'error',
                title: appName,
                text: globalError.value,
            });
        } finally {
            submitting.value = false;
        }
    });

    const finishWizard = async () => {
        const result = await window.Swal.fire({
            title: appName,
            text: '¿Deseas enviar el formulario y crear una inscripción?',
            icon: 'warning',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
        });

        if (!result.isConfirmed) {
            return;
        }

        await submitForm();
    };

    const cancelWizard = async () => {
        const result = await window.Swal.fire({
            title: '¡Atención!',
            text: 'Esta acción borrará la información agregada en el formulario ¿Deseas proceder?',
            icon: 'warning',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No',
        });

        if (!result.isConfirmed) {
            return;
        }

        await resetWizard();
        hideModal();
    };

    return {
        cancelWizard,
        finishWizard,
    };
};
