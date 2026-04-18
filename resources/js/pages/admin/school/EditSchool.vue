<template>
    <SuperAdminSchoolForm mode="edit" />
</template>

<script setup>
import SuperAdminSchoolForm from '@/pages/admin/school/components/SuperAdminSchoolForm.vue'
import { usePageTitle } from '@/composables/use-meta'

usePageTitle('Editar escuela')
</script>
