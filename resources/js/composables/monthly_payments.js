import axios from 'axios';
import { ref } from 'vue';

export default function useMonthlyPayments() {
    const pays = ref([])
    const groups = ref([])
    const categories = ref([])
    const export_excel = ref("")
    const export_pdf = ref("")

    const getGroupsCategories = async (payload) => {
        let response = await axios.get('/v1/groups/training')
        groups.value = response.data.data.groups
        categories.value = response.data.data.categories

    }
    const sendPay = async (payment) => {
        console.log(payment)
        payment._method = "PUT"
        payment[`${payment.column}_amount`] = payment.value
        payment[`${payment.column}`] = payment.selected
        let response = await axios.post(`payments/${payment.id}`, payment)
        return response.data.data ?? response.data.error
    }
    const getPays = async (payload) => {

        let {training_group_id , unique_code, category } = payload

        let response = await axios.get(`/payments?training_group_id=${training_group_id}&unique_code=${unique_code}&category=${category}&dataRaw=true`)

        pays.value = []
        export_excel.value = ''
        export_pdf.value = ''
        pays.value = response.data.rows
        export_excel.value = response.data.url_export_excel
        export_pdf.value = response.data.url_export_pdf
    }

    return {
        pays,
        groups,
        categories,
        export_excel,
        export_pdf,
        getPays,
        sendPay,
        getGroupsCategories,
    }
}