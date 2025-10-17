<template>
    <div class="position-relative" ref="root">
        <div class="form-select form-select-sm d-flex align-items-center justify-content-between flex-wrap"
            :class="{ 'dropdown-toggle': true, disabled: disabled }" role="combobox" :aria-expanded="isOpen.toString()"
            @click="toggleDropdown">
            <div class="d-flex flex-wrap gap-1 align-items-center flex-grow-1"
                :style="multiple ? 'overflow-x: auto;' : ''">
                <template v-if="multiple">
                    <template v-if="selected.length">
                        <span v-for="opt in selected" :key="opt.value"
                            class="badge bg-primary d-flex align-items-center" :title="selected?.label">
                            {{ opt.label }}
                            <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove"
                                @click.stop="removeTag(opt.value)"></button>
                        </span>
                    </template>
                    <span v-else class="text-muted">{{ placeholder }}</span>
                </template>
                <template v-else>
                    <template v-if="selected">
                        <span class="text-truncate" :title="selected.label">
                            {{ selected.label }}
                        </span>
                        <!-- <small class="text-muted">({{ selected.value }})</small> -->
                    </template>
                    <template v-else>
                        <span class="text-muted">{{ placeholder }}</span>
                    </template>
                </template>
            </div>

            <div class="d-flex align-items-center ms-auto">
                <button v-if="clearable && hasValue && !disabled" type="button" class="btn-clear btn-close btn-sm ms-1"
                    @click.stop="clearSelection" aria-label="Clear selection">
                </button>
            </div>
        </div>

        <div class="dropdown-menu w-100 p-2" :class="{ show: isOpen }" style="max-height: 260px; overflow: auto;"
            @click.stop>
            <input ref="searchInput" :id="id" type="search" class="form-control mb-2" :placeholder="searchPlaceholder"
                v-model="query" @keydown.down.prevent="focusNext" @keydown.up.prevent="focusPrev"
                @keydown.enter.prevent="selectFocused" />

            <template v-if="filtered.length === 0">
                <div class="text-muted small p-2">No hay resultados</div>
            </template>

            <ul class="list-group list-group-flush">
                <li v-for="(opt, idx) in filtered" :key="opt.value"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                    :class="{ active: idx === focusedIndex }" @mouseenter="focusedIndex = idx"
                    @mouseleave="focusedIndex = -1" @click="selectOption(opt)" role="option"
                    :aria-selected="isSelected(opt).toString()">
                    <div>
                        <div>{{ opt.label }}</div>
                        <!-- <small class="text-muted">{{ opt.meta || '' }}</small> -->
                    </div>
                    <span v-if="isSelected(opt)" class="badge bg-primary">Seleccionado</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    id: { type: String, default: 'select2' },
    modelValue: { required: false },
    value: { required: false },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Selecciona...' },
    searchPlaceholder: { type: String, default: 'Buscar...' },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    filterFunction: { type: Function, default: null },
    multiple: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'update:value', 'change', 'open', 'close'])
const innerValue = ref(props.modelValue ?? props.value ?? (props.multiple ? [] : null))
const root = ref(null)
const searchInput = ref(null)
const isOpen = ref(false)
const query = ref('')
const focusedIndex = ref(-1)

const hasValue = computed(() =>
    props.multiple
        ? Array.isArray(innerValue.value) && innerValue.value.length > 0
        : !!innerValue.value
)
const selected = computed(() => {
    if (props.multiple) {
        if (!Array.isArray(innerValue.value)) return []
        const selectedOptions = props.options.filter(o => innerValue.value.includes(o.value))
        const missing = innerValue.value
            .filter(v => !selectedOptions.some(o => o.value === v))
            .map(v => ({ value: v, label: String(v) }))
        return [...selectedOptions, ...missing]
    } else {
        if (innerValue.value == null) return null
        const found = props.options.find(o => o.value === innerValue.value)
        return found || { value: innerValue.value, label: String(innerValue.value) }
    }
})

function normalize(str) {
    return String(str).toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '')
}

const filtered = computed(() => {
    if (!query.value) return props.options
    if (props.filterFunction && typeof props.filterFunction === 'function') {
        return props.options.filter(o => props.filterFunction(query.value, o))
    }
    const q = normalize(query.value)
    return props.options.filter(o => {
        return normalize(o.label).includes(q) || normalize(o.value).includes(q) || (o.meta && normalize(o.meta).includes(q))
    })
})

function openDropdown() {
    if (props.disabled) return
    isOpen.value = true
    emit('open')
    setTimeout(() => {
        if (searchInput.value) searchInput.value.focus()
    }, 0)
}
function closeDropdown() {
    isOpen.value = false
    query.value = ''
    focusedIndex.value = -1
    emit('close')
}
function toggleDropdown() {
    if (isOpen.value) closeDropdown(); else openDropdown()
}

function selectOption(opt) {
    if (props.multiple) {
        let newValues = Array.isArray(innerValue.value) ? [...innerValue.value] : []
        if (newValues.includes(opt.value)) {
            newValues = newValues.filter(v => v !== opt.value)
        } else {
            newValues.push(opt.value)
        }
        innerValue.value = newValues
    } else {
        innerValue.value = opt.value
        closeDropdown()
    }
}

function removeTag(val) {
    if (!props.multiple) return
    let newValues = Array.isArray(innerValue.value) ? [...innerValue.value] : []
    newValues = newValues.filter(v => v !== val)
    innerValue.value = newValues
}

function clearSelection() {
    innerValue.value = props.multiple ? [] : null
}

function isSelected(opt) {
    if (props.multiple) {
        return Array.isArray(innerValue.value) && innerValue.value.includes(opt.value)
    }
    return innerValue.value === opt.value
}

function focusNext() {
    if (filtered.value.length === 0) return
    focusedIndex.value = Math.min(filtered.value.length - 1, focusedIndex.value + 1)
    scrollFocusedIntoView()
}
function focusPrev() {
    if (filtered.value.length === 0) return
    focusedIndex.value = Math.max(0, focusedIndex.value - 1)
    scrollFocusedIntoView()
}
function selectFocused() {
    if (focusedIndex.value >= 0 && filtered.value[focusedIndex.value]) {
        selectOption(filtered.value[focusedIndex.value])
    }
}
function scrollFocusedIntoView() {
    requestAnimationFrame(() => {
        const menu = root.value?.querySelector('.dropdown-menu')
        const items = menu?.querySelectorAll('.list-group-item')
        const el = items?.[focusedIndex.value]
        if (el) el.scrollIntoView({ block: 'nearest' })
    })
}

function onDocumentClick(e) {
    if (!root.value) return
    if (!root.value.contains(e.target)) closeDropdown()
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick)
})
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick)
})

watch([filtered, isOpen], () => {
    focusedIndex.value = filtered.value.length ? 0 : -1
})
watch(
    [() => props.modelValue, () => props.value],
    ([newModel, newValue]) => {
        const incoming = newModel ?? newValue
        if (JSON.stringify(incoming) !== JSON.stringify(innerValue.value)) {
            innerValue.value = incoming
        }
    },
    { immediate: true }
)
watch(innerValue, (val) => {
    emit('update:modelValue', val)
    emit('update:value', val)
    emit('change', val)
})
</script>

<style scoped lang="scss">
.form-select.dropdown-toggle {
    // cursor: pointer;
    // height: 45px !important;
    /* altura fija */
    min-height: 45px !important;
    /* asegura consistencia */
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    display: flex;
    align-items: center;
    overflow: hidden;
    /* oculta contenido que exceda la altura */
}

.form-select.dropdown-toggle>div.flex-grow-1 {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 0.25rem;
    overflow: hidden;
    padding-right: 2rem;
    /* espacio para el botón y el ícono */
}

// .form-select.dropdown-toggle>div.flex-grow-1 {
//     position: relative;
//   cursor: text;
//   height: 45px !important;
//   min-height: 45px !important;
//   display: flex;
//   align-items: center;
//   overflow: hidden;
//   padding-right: 2rem; /* espacio para el botón y el ícono */
// }

.btn-clear {
    position: absolute;
    right: 1.75rem;
    top: 30%;
    //   z-index: 2;
}

.btn-clear:hover {
    color: #000;
}

.dropdown-menu.show {
    display: block;
}

.list-group-item.active {
    background-color: rgba(13, 110, 253, 0.12);
}

.badge button {
    font-size: 0.25rem;
    padding: 0.2em 0.5em;
}

.badge:hover {
    transition: none;
    -webkit-transition: none;
    -webkit-transform: none;
    transform: none;
}

.list-group {
    cursor: pointer;
}

.dark {
    .list-group .list-group-item {
        border: 1px solid #151516;
        background-color: #1b2e4b;
        color: #25d5e4;
    }

    .list-group-item-action:hover {
        color: antiquewhite;
    }
}

.list-group-item-action:hover {
    color: #151516;
}

.list-group-item.active {
    background-color: #0d6efd !important;
    color: antiquewhite;
}
</style>