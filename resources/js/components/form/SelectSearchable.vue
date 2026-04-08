<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        required: true,
        // Ejemplo: [{ value: 1, label: "Opción 1" }, { value: 2, label: "Opción 2" }]
    },
    modelValue: {
        type: [String, Number, Object],
        default: null,
    },
    placeholder: {
        type: String,
        default: "Seleccione una opción...",
    },

});

const emit = defineEmits(["update:modelValue"]);

const id = ref(`id_${Math.random().toString(16).slice(2)}`);
const search = ref("");
const isOpen = ref(false);
const dropdownRef = ref(null);

const filteredOptions = computed(() => {
    if (!search.value) return props.options;
    return props.options.filter((opt) =>
        opt.label.toLowerCase().includes(search.value.toLowerCase())
    );
});

const selectOption = (option) => {
    emit("update:modelValue", option);
    isOpen.value = false;
    search.value = "";
}

// Cerrar al hacer click afuera
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
}

const clearSelection = ()  =>{
    search.value = "";
    emit("update:modelValue", null);
    isOpen.value = false;
}

// Actualizar input con el valor seleccionado
watch(() => props.modelValue, (newVal) => {
        search.value = newVal ? newVal.label : "";
    },
    { immediate: true }
);

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <label :for="id" class="form-label">Grupo</label>
    <div class="dropdown w-100 position-relative" ref="dropdownRef">
        <div class="position-relative">

            <!-- Campo de búsqueda / valor seleccionado -->
            <input type="text" class="form-select" v-model="search" :placeholder="props.placeholder"
                @focus="isOpen = true" :id="id"/>

            <button v-if="search" type="button"
                class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-1"
                @click="clearSelection" style="border: none;">
                ✕
            </button>
        </div>

        <perfect-scrollbar v-if="isOpen" class="dropdown-menu show w-100" tag="ul" :options="{suppressScrollX: true}"
            style="position: absolute; margin-top: 25px;">
            <li v-for="option in filteredOptions" :key="option.value">
                <button class="dropdown-item" type="button" @click="selectOption(option)">
                    {{ option.label }}
                </button>
            </li>
            <li v-if="filteredOptions.length === 0" class="dropdown-item disabled">
                No hay resultados
            </li>
        </perfect-scrollbar>
    </div>
</template>