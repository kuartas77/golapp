import { getCurrentInstance, ref } from 'vue'
import api from '@/utils/axios'

const escapeHtml = (value = '') => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

const moneyFormat = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
})

export default function useFinancialClearance() {
    const { proxy } = getCurrentInstance()
    const isGeneratingClearance = ref(false)

    const showDebtDetail = async (status) => {
        const rows = (status.debts || []).map((debt) => `
            <tr>
                <td class="text-start">${escapeHtml(debt.year)}</td>
                <td class="text-start">${escapeHtml(debt.label)}</td>
                <td class="text-end">${escapeHtml(moneyFormat.format(Number(debt.amount || 0)))}</td>
            </tr>
        `).join('')

        await proxy?.$swal?.fire({
            icon: 'warning',
            title: 'No es posible generar el paz y salvo',
            html: `
                <p class="text-start">El deportista tiene obligaciones financieras vencidas:</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th class="text-start">Año</th><th class="text-start">Concepto</th><th class="text-end">Valor</th></tr></thead>
                        <tbody>${rows}</tbody>
                        <tfoot><tr><th colspan="2" class="text-end">Saldo total</th><th class="text-end">${escapeHtml(moneyFormat.format(Number(status.total_debt || 0)))}</th></tr></tfoot>
                    </table>
                </div>
            `,
            confirmButtonText: 'Entendido',
            width: 720,
        })
    }

    const generateFinancialClearance = async (uniqueCode) => {
        if (isGeneratingClearance.value || !uniqueCode) return

        isGeneratingClearance.value = true

        try {
            const endpoint = `/api/v2/players/${encodeURIComponent(uniqueCode)}/financial-clearance`
            const { data } = await api.get(endpoint)

            if (!data.eligible) {
                await showDebtDetail(data)
                return
            }

            if (Number(data.credit_balance || 0) > 0) {
                await proxy?.$swal?.fire({
                    icon: 'info',
                    title: 'Saldo a favor disponible',
                    text: `El deportista tiene ${moneyFormat.format(Number(data.credit_balance || 0))} de saldo a favor. El paz y salvo se puede generar porque no registra obligaciones vencidas.`,
                    confirmButtonText: 'Generar paz y salvo',
                })
            }

            window.open(`${endpoint}/pdf`, '_blank', 'noopener')
        } catch (error) {
            await proxy?.$swal?.fire({
                icon: 'error',
                title: 'No fue posible verificar el paz y salvo',
                text: error.response?.data?.message || 'Inténtalo nuevamente.',
                confirmButtonText: 'Entendido',
            })
        } finally {
            isGeneratingClearance.value = false
        }
    }

    return {
        isGeneratingClearance,
        generateFinancialClearance,
    }
}
