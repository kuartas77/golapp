import axios from 'axios';
import {ref} from 'vue';

export default function usePayouts() {
    const rows = ref([])
    const groups = ref([])
    const tournaments = ref([])
    const paginationMeta = ref([])

    const fetchRows = async () => {
        let response = await axios.get('/api/schools')
        rows.value = response.data.data
        paginationMeta.value = response.data.meta
    }

    const fetchRowsPaginate = async () => {
        let current_page = paginationMeta.value.current_page
        let pageNum = current_page ? current_page : 1

        let response = await axios.get(`/api/schools?page=${pageNum}`)
        rows.value = response.data.data
        paginationMeta.value = response.data.meta
    }

    const loadTournaments = async () => {
        let response = await axios.get('/autocomplete/tournaments')
        tournaments.value = response.data.data
    }

    const loadGroups = async (tournament_id) => {
        let response = await axios.get(`/autocomplete/competition_groups?tournament_id=${tournament_id}`)
        groups.value = response.data.data
    }

    return {
        rows,
        groups,
        tournaments,
        paginationMeta,
        fetchRows,
        fetchRowsPaginate,
        loadTournaments,
        loadGroups
    }
}