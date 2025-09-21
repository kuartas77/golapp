import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { routeName } from '@/composables/routeName';

export default function useSettings() {
    const groups = ref([])
    const categories = ref([])
    const router = useRouter()

    routeName()

    const getSettings = async () => {
        let response = await axios.get('settings/general')
        groups.value = response.data.t_groups
        categories.value = response.data.categories
    }

    const resolveRouteFromClick = (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()
        router.push('/inscripciones/' + itemId);
    }

    onMounted(() => {
        getSettings()
        if (inscription_table.value) {
            let dt = inscription_table.value.dt;
            const selectGroups = document.querySelector('thead select[placeholder="Grupos"]');
            if (selectGroups) {
                selectGroups.addEventListener('change', function () {
                    return dt.column(3).search(this.value).draw()
                });
            }
            const selectCategories = document.querySelector('thead select[placeholder="Categorias"]');
            if (selectCategories) {
                selectCategories.addEventListener('change', function () {
                    return dt.column(4).search(this.value).draw()
                });
            }
        }
    });

    return {
        getSettings,
        resolveRouteFromClick,
        groups,
        categories
    }
}
