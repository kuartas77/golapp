import { ref } from 'vue';

export const usePlayerDocumentLookup = ({
    api,
    endpoints,
}) => {
    const autocomplete = ref({
        school: [],
        place_birth: [],
        neighborhood: [],
        eps: [],
    });

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

    return {
        autocomplete,
        fetchAutocompleteOptions,
    };
};
