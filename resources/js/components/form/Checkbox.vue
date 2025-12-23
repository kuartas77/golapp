<template>
    <div class="form-group">
        <div class="form-check ps-0">
            <div class="custom-control custom-checkbox checkbox-primary">
                <input
                    :type="type"
                    :id="name"
                    class="custom-control-input"
                    :class="{ field_error: errorMessage, valid: meta.valid }"
                    :checked="isChecked"
                    @change="handleChange"
                    v-bind="$attrs"
                />
                <label class="custom-control-label" :for="name">{{ label }}</label>
                <div :class="errorMessage ? 'custom-error' : ''">{{ errorMessage }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useField } from 'vee-validate'
import { computed } from 'vue'

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'checkbox'
    },
    label: String,
    returnValueType: {
        type: String,
        default: 'boolean', // 'boolean' o 'number'
        validator: (value) => ['boolean', 'number'].includes(value)
    }
})

const { value, errorMessage, meta } = useField(() => props.name)

// Computed para determinar si el checkbox debe estar checked
const isChecked = computed(() => {
    if (props.returnValueType === 'number') {
        return value.value === 1
    }
    return Boolean(value.value) // Convertir cualquier valor truthy a true
})

// Manejar el cambio del checkbox
const handleChange = (event) => {
    if (props.returnValueType === 'number') {
        value.value = event.target.checked ? 1 : 0
    } else {
        value.value = event.target.checked
    }
}
</script>