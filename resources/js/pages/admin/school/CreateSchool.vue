<template>
    <SuperAdminSchoolForm mode="create" />
</template>

<script setup>
import SuperAdminSchoolForm from '@/pages/admin/school/components/SuperAdminSchoolForm.vue'
import { usePageTitle } from '@/composables/use-meta'

usePageTitle('Crear escuela')
</script>
