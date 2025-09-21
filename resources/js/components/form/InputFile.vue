<template>
	<img :src="preview" class="img-thumbnail" style="max-width: 200px; max-height: 172px;" alt="Vista previa">
	<div class="form-group">

		<label v-if="label" :for="name" class="form-label">{{ label }}</label>

		<input type="file" class="form-control form-control-sm" :id="name" :accept="accept" @change="onFileChange">

	</div>

	<div :class="errorMessage ? 'custom-error' : ''">{{ errorMessage }}</div>

</template>
<script setup>
import { ref, watch } from "vue";
import { useField } from 'vee-validate'
const props = defineProps({
	name: { type: String, required: true },
	label: { type: String, default: "Selecciona un archivo" },
	accept: { type: String, default: "image/png, image/jpeg" },
})

const { value, errorMessage, handleChange } = useField(props.name);
const preview = ref('http://golapp.local/img/ballon_dark.png');

// Actualizar preview cuando cambia el valor
watch(value, (file) => {
	if (file instanceof File) {
		preview.value = URL.createObjectURL(file);
	} else {
		preview.value = isValidUrl(file) ? file : 'http://golapp.local/img/ballon_dark.png';
	}
});

const onFileChange = (e) => {
	const file = e.target.files[0];
	handleChange(e); // notificar a vee-validate
};

const isValidUrl = (urlString) => {
	try {
		new URL(urlString);
		return true;
	} catch (error) {
		return false;
	}
}

</script>