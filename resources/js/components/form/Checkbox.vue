<template>
    <div class="form-group">
        <div class="form-check ps-0">
            <div class="custom-control custom-checkbox checkbox-primary">
                <input :type="type" :id="name" class="custom-control-input"
                    :class="{ field_error: errorMessage, valid: meta.valid }" v-model="value" v-bind="$attrs" />
                <label class="custom-control-label" :for="name">{{ label }}</label>
                <div :class="errorMessage ? 'custom-error' : ''">{{ errorMessage }}</div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { useField } from 'vee-validate'
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
})

const { value, errorMessage, meta } = useField(() => props.name)
</script>