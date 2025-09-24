import axios from 'axios';
import {ref} from 'vue';

export default function usePayouts() {
    const rows = ref([])
    const groups = ref([])
    const tournaments = ref([])
    const paginationMeta = ref([])
    const pays = ref([])

    const fetchRows = async () => {
        let response = await axios.get('/api/v2/schools')
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
        pays.value = []
        let response = await axios.get(`/v1/tournamentpayout?tournament_id=${tournament_id}&competition_group_id=${competition_group_id}&unique_code=${unique_code}&dataRaw=true`)
        pays.value = response.data.data
        return pays
    }

    const sendPay = async (payment) => {
        payment._method = "PUT"
        payment.status = payment.selected
        let response = await axios.post(`/tournamentpayout/${payment.id}`, payment)
        return response.data.data ?? response.data.error
    }

    const createPayments = async (payload) => {
        let response = await axios.post(`/tournamentpayout`, payload)
        return response.data.data
    }

    return {
        pays,
        rows,
        groups,
        tournaments,
        paginationMeta,
        fetchRows,
        loadTournaments,
        loadGroups,
        getPays,
        sendPay,
        createPayments
    }
}