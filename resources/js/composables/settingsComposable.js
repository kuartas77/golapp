import axios from 'axios';
import { ref } from 'vue';


export default function useSettings() {
    const groups = ref([])
    const categories = ref([])

    const getSettings = async () => {
        let response = await axios.get('settings/general')
        groups.value = response.data.t_groups
        categories.value = response.data.categories
    }

    return {
        getSettings,
        groups,
        categories
    }
}
