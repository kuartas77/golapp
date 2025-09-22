import axios from 'axios';
import { ref, onMounted } from 'vue';

import { routeName } from '@/composables/routeName';

export default function useSettings() {
    const groups = ref([])
    const categories = ref([])

    const getSettings = async () => {
        let response = await axios.get('settings/general')
        groups.value = response.data.t_groups
        categories.value = response.data.categories
    }

    onMounted(() => {
        routeName()
        getSettings()
    });

    return {
        getSettings,
        groups,
        categories
    }
}
