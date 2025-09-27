import { ref, onMounted } from 'vue';
import { useSetting } from '@/store/settings-store';
import api from "@/utils/axios";

export default function useSettings() {

    const settings = useSetting()

    // const groups = ref([])
    // const categories = ref([])
    // const genders = ref([])
    // const positions = ref([])
    // const blood_types = ref([])
    // const averages = ref([])
    // const dominant_profile = ref([])
    // const relationships = ref([])
    // const competition_groups = ref([])
    // const inscription_years = ref([])
    // const document_types = ref([])
    // const jornada = ref([])
    // const schools = ref([])

    // const getSettings = async () => {
    //     const response = await api.get('api/v2/settings/general')
    //     groups.value = response.data.t_groups
    //     categories.value = response.data.categories
    //     genders.value = response.data.genders
    //     positions.value = response.data.positions
    //     blood_types.value = response.data.blood_types
    //     averages.value = response.data.averages
    //     dominant_profile.value = response.data.dominant_profile
    //     relationships.value = response.data.relationships
    //     competition_groups.value = response.data.competition_groups
    //     inscription_years.value = response.data.inscription_years
    //     document_types.value = response.data.document_types
    //     jornada.value = response.data.jornada
    //     schools.value = response.data.schools
    // }

    onMounted(async() => {
        settings.getSettings()
    });

    return {settings}
}
