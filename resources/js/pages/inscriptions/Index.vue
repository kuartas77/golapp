<template>
    <panel>
        <template #body>
            <DataTable :columns="columns" :options="options" ajax="datatables/inscriptions_enabled"
                class="table table-bordered table-sm" id="inscription_table" ref="inscription_table"
                @click="resolveRouteFromClick($event)">

                <thead>
                    <tr>
                        <th></th>
                        <th>Código</th>
                        <th>Doc</th>
                        <th id="select_groups"></th>
                        <th id="select_categories"></th>
                        <th>Nombres</th>
                        <th>Registro</th>
                    </tr>
                </thead>


                <template #photo="props">
                    <div class="avatar avatar-sm me-1">
                        <img :src="props.cellData" alt="avatar" class="rounded-circle">
                    </div>
                </template>

                <template #link="props">
                    <div class="text-center">
                        <a href="#" :data-item-id="props.cellData" class="link-primary">{{
                            props.rowData.unique_code }}</a>
                    </div>
                </template>

                <template #date="props">
                    <div class="text-center">
                        {{ dayjs(props.cellData).format('l') }}
                    </div>
                </template>

            </DataTable>
        </template>
    </panel>

    <teleport defer to="#select_groups">
        <select placeholder="Grupos" id="groups" name="groups">
            <option value="">Grupos...</option>
            <option v-for="group in groups" :value="group.id" :key="group.id">{{ group.name }}
            </option>
        </select>
    </teleport>

    <teleport defer to="#select_categories">
        <select placeholder="Categorias" id="categories" name="categories">
            <option value="">Categorias...</option>
            <option v-for="category in categories" :value="category.category" :key="category.category">{{
                category.category
            }}</option>
        </select>
    </teleport>

    <breadcrumb :active="'Listado'" />
</template>
<script setup lang="ts">
import dayjs from "@/utils/dayjs";
import { inscription_table, columns, options } from '@/composables/inscriptionList';
import useSettings from "@/composables/settingsComposable";

const { resolveRouteFromClick, groups, categories } = useSettings();
</script>