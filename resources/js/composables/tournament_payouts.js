import axios from 'axios';
import {ref} from 'vue';

export default function usePayouts() {
    const rows = ref([])
    const groups = ref([])
    const tournaments = ref([])
    const paginationMeta = ref([])
    const pays = ref([])

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


    const getPays = async ({competition_group_id, tournament_id, unique_code}) => {
        let response = await axios.get(`/tournamentpayout?tournament_id=${tournament_id}&competition_group_id=${competition_group_id}&unique_code=${unique_code}`)
        pays.value = response.data.data
        return pays
    }

    return {
        pays,
        rows,
        groups,
        tournaments,
        paginationMeta,
        fetchRows,
        // fetchRowsPaginate,
        loadTournaments,
        loadGroups,
        getPays
    }
}