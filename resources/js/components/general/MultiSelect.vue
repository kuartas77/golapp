<template>
    <div class="ms-container responsive-ms d-flex justify-content-between align-items-stretch flex-nowrap gap-3">

        <select :id="id" class="form-select d-none" multiple v-model="selectedValues" name="selectedOptions">
            <option v-for="opt in selectedOptions" :key="opt.id" :value="opt.id">
                {{ opt.name }}
            </option>
        </select>

        <div class="ms-selectable d-flex flex-column flex-fill" :class="{ 'ms-focus': activeList === 'available' }"
            @click="setActive('available')">
            <ul class="ms-list flex-grow-1">
                <li v-for="option in sortedAvailable" :key="option.id" class="ms-elem-selectable"
                    @click.stop="addSelection(option)">
                    {{ option.name }}
                </li>
            </ul>

            <div v-if="buttons" class="ms-buttons text-center p-2">
                <button type="button" class="btn btn-primary btn-sm" @click.stop="addAll"
                    :disabled="sortedAvailable.length === 0" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="Agregar todos!">
                    >>
                </button>
            </div>
        </div>

        <div class="ms-selection d-flex flex-column flex-fill" :class="{ 'ms-focus': activeList === 'selected' }"
            @click="setActive('selected')">
            <ul class="ms-list flex-grow-1">
                <li v-for="option in sortedSelected" :key="option.id" class="ms-elem-selection"
                    @click.stop="removeSelection(option)">
                    {{ option.name }}
                </li>
            </ul>

            <div v-if="buttons" class="ms-buttons text-center p-2">
                <button type="button" class="btn btn-primary btn-sm" @click.stop="removeAll"
                    :disabled="sortedSelected.length === 0" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="Quitar todos!">
                    << </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

// Props y emisión estándar (para usar con v-model o vee-validate)
const props = defineProps({
    id: {
        type: String,
        default: 'multiSelect'
    },
    modelValue: {
        type: Array,
        default: () => []
    },
    value: {
        type: Array,
        default: () => []
    },
    options: {
        type: Array,
        required: true
    },
    buttons: {
        type: Boolean,
        default: false
    }
})
const emit = defineEmits(['update:modelValue', 'update:value'])

const selectedOptions = ref([])
const activeList = ref(null)

const availableOptions = computed(() =>
    props.options.filter(opt => !selectedOptions.value.some(sel => sel.id === opt.id))
)

const sortedAvailable = computed(() =>
    [...availableOptions.value].sort((a, b) => a.id - b.id)
)

const sortedSelected = computed(() =>
    [...selectedOptions.value].sort((a, b) => a.id - b.id)
)

const selectedValues = computed({
    get: () => selectedOptions.value.map(opt => opt.id),
    set: (ids) => {
        selectedOptions.value = props.options.filter(opt => ids.includes(opt.id))
        emitUpdates()
    }
})

function emitUpdates() {
    emit('update:modelValue', selectedOptions.value)
    emit('update:value', selectedOptions.value)
}

function addSelection(item) {
    selectedOptions.value = [...selectedOptions.value, item]
    emitUpdates()
}

function removeSelection(item) {
    selectedOptions.value = selectedOptions.value.filter(opt => opt.id !== item.id)
    emitUpdates()
}

function addAll() {
    selectedOptions.value = [...props.options]
    emitUpdates()
}

function removeAll() {
    selectedOptions.value = []
    emitUpdates()
}

function setActive(list) {
    activeList.value = list
}

// Carga inicial correcta (tanto de modelValue como de value)
onMounted(() => {
    const initial = props.modelValue.length ? props.modelValue : props.value
    selectedOptions.value = [...initial]
})

// Reacciona cuando cambia desde fuera
watch(() => props.modelValue, (val) => {
    if (val && val !== selectedOptions.value) {
        selectedOptions.value = [...val]
    }
})

watch(() => props.value, (val) => {
    if (val && val !== selectedOptions.value) {
        selectedOptions.value = [...val]
    }
})
</script>

<style scoped>
/* ==== Responsivo y altura flexible ==== */

.responsive-ms {
    width: 100%;
    max-width: 100%;
    flex-wrap: nowrap;
    /* siempre lado a lado */
    background-size: contain;
    background-position: center bottom;
    min-height: 250px;
    /* altura base ajustable */
}

/* ambas columnas comparten espacio 50/50 */
.ms-selectable,
.ms-selection {
    width: 48% !important;
    display: flex;
    flex-direction: column;
}

/* la lista crece para ocupar el espacio disponible */
.ms-list {
    flex-grow: 1;
    overflow-y: auto;
    min-height: 200px;
}

/* En pantallas muy pequeñas, conserva lado a lado pero ajusta tamaños */
@media (max-width: 576px) {
    .responsive-ms {
        gap: 0.5rem;
    }

    .ms-selectable,
    .ms-selection {
        justify-content: center;
        width: 50% !important;
    }

    .ms-list {
        min-height: 150px;
    }

    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }
}
</style>