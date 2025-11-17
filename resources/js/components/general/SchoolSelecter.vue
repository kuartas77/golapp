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
        <a v-else href="javascript:;" class="d-flex align-items-center">
            <span class="ms-2">{{ schoolSelected }}</span>
        </a>
    </div>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import api from '@/utils/axios';

const schoolSelected = ref('')
const isSchool = ref(false);
const text = ref(isSchool.value ? 'sede' : 'escuela');
const schoolOptions = ref([])

function selectSchool() {
    Swal.fire({
        title: `Para seguir seleciona una ${text.value}`,
        icon: "info",
        input: 'select',
        inputOptions: schoolOptions.value,
        inputPlaceholder: 'Selecciona...',
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
    }).then(function (result) {
        if (result.value) {
            api.post('/api/v2/admin/change_school', { 'school_id': result.value })
                .then(() => setTimeout(location.reload(), 2000))
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