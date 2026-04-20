<template>
    <div class="dark-mode d-flex align-items-center">
        <a href="javascript:;" class="d-flex align-items-center" @click="selectSchool"
            v-if="schoolOptions.length || !isSchool">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-refresh-cw">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            <span class="ms-2">{{ schoolSelected }}</span>
        </a>
        <span v-else class="d-flex align-items-center">
            <span class="ms-2">{{ schoolSelected }}</span>
        </span>
    </div>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '@/utils/axios';
import { useAuthUser } from '@/store/auth-user';
import { useSetting } from '@/store/settings-store';

const schoolSelected = ref('')
const isSchool = ref(false);
const schoolOptions = ref([])
const auth = useAuthUser()
const settings = useSetting()
const text = computed(() => (isSchool.value ? 'Sede' : 'Escuela'))

function selectSchool() {

    Swal.fire({
        title: `Para seguir seleciona`,
        icon: "info",
        input: 'select',
        inputLabel: `${text.value}`,
        inputOptions: schoolOptions.value,
        // inputPlaceholder: 'Selecciona...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: "Cancelar",
        inputValidator: function (value) {
            return new Promise(function (resolve) {
                if (value !== '') {
                    resolve();
                } else {
                    resolve(`Necesitas seleccionar una ${text.value}`);
                }
            });
        }
    }).then(async function (result) {
        if (result.isConfirmed && result.value) {
            try {
                await api.post('/api/v2/admin/change_school', { school_id: result.value })
                auth.clearState()
                settings.clearState()
                window.location.reload()
            } catch (error) {
                console.log(error)
                Swal.fire({
                    icon: 'error',
                    title: 'No fue posible cambiar la escuela',
                    text: 'Intenta nuevamente en unos segundos.'
                })
            }
        }
    });
}

const getInfoCampus = async () => {
    api.get('/api/v2/admin/info_campus').then((response) => {
        const { is_school, schools, school_selected } = response.data
        isSchool.value = is_school
        schoolOptions.value = Object.fromEntries(schools.map(item => [item.id, item.name])),
            schoolSelected.value = school_selected
    }).catch(console.log)
}

onMounted(async () => {
    getInfoCampus()
})
</script>
