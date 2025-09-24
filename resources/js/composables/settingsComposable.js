import { ref, onMounted } from 'vue';
import api from "@/utils/axios";

export default function useSettings() {
    const groups = ref([])
    const categories = ref([])

    const getSettings = async () => {
        const response = await api.get('api/v2/settings/general')
        groups.value = response.data.t_groups
        categories.value = response.data.categories
    }

    onMounted(() => {
        getSettings()
    });

    return {
        getSettings,
        groups,
        categories
    }
}
