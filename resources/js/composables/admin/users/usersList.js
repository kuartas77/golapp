import configLanguaje from '@/utils/datatableUtils'
import { getCurrentInstance, ref, useTemplateRef, onMounted } from 'vue'
import { usePageTitle } from "@/composables/use-meta"
import api from '@/utils/axios'
import * as yup from 'yup'

export default function useUsersList() {

    const table = useTemplateRef('table')
    const form = useTemplateRef('form')

    const columns = [
        { data: 'user_name', title: 'Nombres', searchable: true, orderable: true },
        { data: 'role_name', title: 'Perfil', name: 'roles.name', searchable: true, orderable: true },
        { data: 'email', title: 'Correo', searchable: true, orderable: false },
        { data: 'created_at', title: 'Registro', name: 'users.created_at', render: '#date', searchable: false, orderable: false },
        { data: 'id', title: 'Acciones', render: '#actions', searchable: false, orderable: false },
    ]

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 2, targets: columns.length - 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            }
        ],
        // scrollX: true,
        serverSide: true,
        pipeline: { pages: 5 },
        processing: true,
        order: [[1, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/users_enabled', { params: data }) // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                })
            } catch (error) {
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 })
            }
        },
        columns: columns
    }

    const { proxy } = getCurrentInstance()
    const globalError = ref(null)

    const composeModalUser = ref(null)
    const profileModal = ref(null)
    const selectedProfile = ref(null)
    const profileLoading = ref(false)
    const profileError = ref('')
    const initialData = ref({
        id: null,
        name: null,
        email: null,
        rol_id: null
    })

    const schema = yup.object().shape({
        id: yup.mixed().nullable().optional(),
        name: yup.string().required(),
        email: yup.string().email().required(),
        rol_id: yup.number().required()
    })

    const onCancel = () => {
        modalHidden()
        composeModalUser.value.hide()
        form.value.resetForm()
    }
    const submit = async (values, actions) => {
        try {
            let userData = { ...values }

            if (userData.id) {
                userData._method = 'PUT'
                await api.post(`/api/v2/admin/users/${userData.id}`, userData)
            } else {
                await api.post(`/api/v2/admin/users`, userData)
            }

            if (table.value) {
                let dt = table.value.table.dt;
                dt.clearPipeline()
                dt.ajax.reload(null, false)
            }

            modalHidden()
            composeModalUser.value.hide()
            showMessage('Guardado correctamente')
        } catch (error) {
            proxy.$handleBackendErrors(error, actions.setErrors, (msg) => (globalError.value = msg))
        } finally {
            form.value.resetForm()
        }
    }

    const editUser = async (itemId) => {
        if (!itemId) {
            return
        }
        const response = await api.get(`/api/v2/admin/users/${itemId}`)

        const data = {
            id: itemId,
            name: response.data.data.name,
            email: response.data.data.email,
            rol_id: response.data.data.role.id
        }

        form.value.resetForm()
        form.value.setValues(data)

        composeModalUser.value.show()


    }

    const showProfile = async (userId) => {
        profileLoading.value = true
        profileError.value = ''
        selectedProfile.value = null
        profileModal.value.show()

        try {
            const response = await api.get(`/api/v2/admin/users/${userId}/profile`)
            selectedProfile.value = response.data.data
        } catch (error) {
            profileError.value = error.response?.data?.message || 'No fue posible cargar el perfil.'
        } finally {
            profileLoading.value = false
        }
    }

    const closeProfile = () => {
        profileModal.value.hide()
        selectedProfile.value = null
        profileError.value = ''
    }

    onMounted(() => {
        usePageTitle('Usuarios')
        composeModalUser.value = new window.bootstrap.Modal(document.getElementById("composeModalUser"), {
            backdrop: 'static', // Prevents closing the modal by clicking outside
            keyboard: false,    // Disables closing the modal with the escape key
            focus: false         // Focuses the modal when initialized (default is true)
        })
        profileModal.value = new window.bootstrap.Modal(document.getElementById("profileModalUser"), {
            backdrop: true,
            keyboard: true,
            focus: true
        })
    })

    return {
        table,
        options,
        initialData,
        schema,
        editUser,
        onCancel,
        submit,
        selectedProfile,
        profileLoading,
        profileError,
        showProfile,
        closeProfile,
    }
}
