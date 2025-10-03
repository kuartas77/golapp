<template>
	<div class="upload mt-4 pe-md-4">
		<input ref="fl_profile" type="file" class="d-none" :id="name" :accept="accept" @change="onFileChange" />
		<img :src="preview ? preview : 'http://golapp.local/img/ballon_dark.png'" alt="Logo Escuela"
			class="profile-preview" @click="$refs.fl_profile.click()" />
		<p class="mt-2"  v-if="label">
			<label v-if="label" :for="name" class="form-label">{{ label }}</label>
		</p>
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
const preview = ref(null);
// Actualizar preview cuando cambia el valor
watch(value, (file) => {
	if (file instanceof File) {
		preview.value = URL.createObjectURL(file);
	} else {
		preview.value = file
	}
});

const onFileChange = (e) => {
	const file = e.target.files[0];
	handleChange(e); // notificar a vee-validate
};

</script>