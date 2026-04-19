import {ref} from 'vue';
import api from '@/utils/axios'

export default function usePayouts() {
    const rows = ref([])
    const groups = ref([])
    const tournaments = ref([])
    const paginationMeta = ref([])
    const pays = ref([])

    const fetchRows = async () => {
        let response = await api.get('/api/v2/schools')
        rows.value = response.data.data
        paginationMeta.value = response.data.meta
    }

    const loadTournaments = async () => {
        let response = await api.get('/api/v2/autocomplete/tournaments')
        tournaments.value = response.data.data
    }

    const loadGroups = async (tournament_id) => {
        let response = await api.get('/api/v2/autocomplete/competition_groups', {
            params: {
                tournament_id,
            },
        })
        groups.value = response.data.data
    }


    const getPays = async ({competition_group_id, tournament_id, unique_code}) => {
        pays.value = []
        let response = await api.get('/api/v2/tournament-payouts', {
            params: {
                tournament_id,
                competition_group_id,
                unique_code,
                dataRaw: true,
            },
        })
        pays.value = response.data.data
        return pays
    }

    const sendPay = async (payment) => {
        payment.status = payment.selected
        let response = await api.put(`/api/v2/tournament-payouts/${payment.id}`, payment)
        return response.data.data ?? response.data.error
    }

    const createPayments = async (payload) => {
        let response = await api.post('/api/v2/tournament-payouts', payload)
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
