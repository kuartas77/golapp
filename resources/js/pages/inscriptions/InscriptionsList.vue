<template>
    <panel>
        <template #body>
            <DatatableTemplate :id="'inscription_table'" :options="options" ref="inscription_table"
                @click="resolveRouteFromClick($event)">
                <template #thead>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Código</th>
                            <th id="select_groups"></th>
                            <th id="select_categories"></th>
                            <th>Genero</th>
                            <th>Nombres</th>
                            <th>F.Inicio</th>
                        </tr>
                    </thead>
                </template>
            </DatatableTemplate>
        </template>
    </panel>

    <teleport defer to="#select_groups">
        <select placeholder="Grupos" id="groups" name="groups">
            <option value="">Grupos...</option>
            <option v-for="group in settings.all_groups" :value="group.id" :key="group.id">{{ group.name }}
            </option>
        </select>
    </teleport>

    <teleport defer to="#select_categories">
        <select placeholder="Categorias" id="categories" name="categories">
            <option value="">Categorias...</option>
            <option v-for="category in settings.categories" :value="category.category" :key="category.category">
                {{ category.category }}
            </option>
        </select>
    </teleport>

    <breadcrumb :parent="'Plataforma'" :current="'Inscripciones'" />
</template>
<script setup>
import { useSetting } from '@/store/settings-store';
import useInscriptionList from '@/composables/inscription/inscriptionList';
import { usePageTitle } from "@/composables/use-meta";
usePageTitle('Inscripciones')
const settings = useSetting()
const { inscription_table, options, resolveRouteFromClick } = useInscriptionList()
</script>