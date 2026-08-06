import { onBeforeUnmount, ref, watch } from 'vue';

const normalizeDate = (value) => {
    const match = String(value ?? '').match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
};

const hasAutocompleteValue = (value) => (
    value !== null && value !== undefined && String(value).trim() !== ''
);

export const usePlayerDocumentLookup = ({
    api,
    endpoints,
    schoolId,
    values,
    setFieldValue,
    normalizeEmail,
}) => {
    const autocomplete = ref({
        school: [],
        place_birth: [],
        neighborhood: [],
        eps: [],
    });

    let lookupTimeout = null;
    let lookupCounter = 0;

    const populatePlayerData = (player) => {
        if (!player || typeof player !== 'object') {
            return;
        }

        const fieldsToPopulate = {
            names: player.names,
            last_names: player.last_names,
            date_birth: normalizeDate(player.date_birth),
            place_birth: player.place_birth,
            document_type: player.document_type ? String(player.document_type) : '',
            gender: player.gender ? String(player.gender) : '',
            email: normalizeEmail(player.email),
            mobile: player.mobile,
            medical_history: player.medical_history,
            address: player.address,
            municipality: player.municipality,
            neighborhood: player.neighborhood,
            rh: player.rh ? String(player.rh) : '',
            eps: player.eps,
            student_insurance: player.student_insurance,
            school: player.school,
            degree: player.degree !== null && player.degree !== undefined ? String(player.degree) : '',
            jornada: player.jornada ? String(player.jornada) : '',
        };

        Object.entries(fieldsToPopulate).forEach(([field, value]) => {
            if (hasAutocompleteValue(value)) {
                setFieldValue(field, value);
            }
        });
    };

    const fetchAutocompleteOptions = async () => {
        try {
            const response = await api.get(endpoints.autocomplete, {
                params: {
                    fields: ['school', 'place_birth', 'neighborhood', 'eps', 'commune'],
                },
            });

            autocomplete.value = {
                school: response.data?.school ?? response.data?.data?.school ?? [],
                place_birth: response.data?.place_birth ?? response.data?.data?.place_birth ?? [],
                neighborhood: response.data?.neighborhood ?? response.data?.data?.neighborhood ?? [],
                eps: response.data?.eps ?? response.data?.data?.eps ?? [],
            };
        } catch (error) {
            autocomplete.value = {
                school: [],
                place_birth: [],
                neighborhood: [],
                eps: [],
            };
        }
    };

    const lookupDocument = async (documentNumber) => {
        const requestId = ++lookupCounter;

        try {
            const response = await api.get(endpoints.searchDoc, {
                params: {
                    doc: documentNumber,
                    school_id: schoolId,
                },
            });

            if (requestId !== lookupCounter || String(values.identification_document).trim() !== documentNumber) {
                return;
            }

            populatePlayerData(response.data?.data);
        } catch (error) {
            // Preserve the values already entered when the lookup fails.
        }
    };

    watch(
        () => values.identification_document,
        (currentValue) => {
            const documentNumber = String(currentValue ?? '').trim();

            clearTimeout(lookupTimeout);

            if (!/^\d{8,}$/.test(documentNumber)) {
                return;
            }

            lookupTimeout = window.setTimeout(() => {
                lookupDocument(documentNumber);
            }, 400);
        }
    );

    onBeforeUnmount(() => {
        clearTimeout(lookupTimeout);
    });

    return {
        autocomplete,
        fetchAutocompleteOptions,
    };
};
