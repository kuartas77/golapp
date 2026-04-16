<template>
    <div class="form-group">
        <label :for="name" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger">&nbsp;(*)</span>
        </label>

        <input
            :id="name"
            :name="name"
            ref="inputRef"
            :accept="accept"
            class="form-control-file"
            :class="{ 'is-invalid': !meta.valid && errorMessage }"
            type="file"
            @change="onFileChange"
        >

        <small v-if="selectedFileName" class="form-text text-muted">
            {{ selectedFileName }}
        </small>

        <div :class="errorMessage ? 'invalid-feedback d-block' : ''">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useField } from 'vee-validate';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    accept: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const inputRef = ref(null);
const {
    value,
    meta,
    errorMessage,
    handleChange,
    setTouched,
} = useField(() => props.name, undefined, {
    type: 'file',
    validateOnValueUpdate: true,
    keepValueOnUnmount: true,
});

const isFileValue = (file) => typeof File !== 'undefined' && file instanceof File;

const selectedFileName = computed(() => (
    isFileValue(value.value) ? value.value.name : ''
));

const onFileChange = async (event) => {
    setTouched(true);
    handleChange(event, true);
};

watch(value, (currentValue) => {
    if (!currentValue && inputRef.value) {
        inputRef.value.value = '';
    }
});
</script>
