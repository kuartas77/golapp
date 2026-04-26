<template>
	<div class="upload text-center">
		<input ref="fl_profile" type="file" class="d-none" :id="name" :accept="accept" @change="onFileChange" />

		<div class="preview-wrapper">
			<img :src="preview ? preview : defaultPreview" alt="Logo Escuela" class="profile-preview"
				@click="$refs.fl_profile.click()" />
		</div>

		<div v-if="previewObjectUrl" class="mt-2 d-flex justify-content-center gap-2 flex-wrap">

			<button type="button" class="btn btn-sm btn-outline-secondary" @click="rotateImage(90)"
				:disabled="isProcessing || !canRotate">
				↻
			</button>

			<button type="button" class="btn btn-sm btn-outline-secondary" @click="rotateImage(-90)"
				:disabled="isProcessing || !canRotate">
				↺
			</button>
		</div>

		<p class="mt-2" v-if="label">
			<label :for="name" class="form-label">{{ label }}</label>
		</p>
	</div>

	<div :class="!meta.valid ? 'custom-error' : ''">{{ errorMessage }}</div>
</template>
<script setup>
import { ref, watch, onBeforeUnmount, computed } from "vue";
import { useField } from "vee-validate";

const props = defineProps({
	name: { type: String, required: true },
	label: { type: String, default: "Selecciona un archivo" },
	accept: { type: String, default: "image/png, image/jpeg, image/webp" },
	defaultPreview: { type: String, default: "/img/ball-dark.webp" },
});

const { value, meta, errorMessage, handleChange, setValue } = useField(props.name);

const preview = ref(null);
const isProcessing = ref(false);
const initialPreviewUrl = ref(null);

let previewObjectUrl = null;
const canRotate = computed(() => value.value instanceof File);

const revokePreviewUrl = () => {
	if (previewObjectUrl) {
		URL.revokeObjectURL(previewObjectUrl);
		previewObjectUrl = null;
	}
};

const isUrlPreview = (fileOrPath) => {
	return typeof fileOrPath === "string" && fileOrPath.trim() !== "";
};

const setPreviewFromValue = (fileOrPath) => {
	revokePreviewUrl();

	if (fileOrPath instanceof File) {
		previewObjectUrl = URL.createObjectURL(fileOrPath);
		preview.value = previewObjectUrl;
		return;
	}

	if (isUrlPreview(fileOrPath)) {
		initialPreviewUrl.value = fileOrPath;
		preview.value = fileOrPath;

		// Limpia el valor real del field para no enviar la URL al backend
		if (value.value !== null) {
			setValue('');
		}
		return;
	}

	preview.value = initialPreviewUrl.value || null;
};

watch(value, (file) => {
	setPreviewFromValue(file);
}, { immediate: true });

const onFileChange = (e) => {
	const file = e.target.files?.[0];

	if (!file) {
		return;
	}

	handleChange(file);
};

const loadImage = (src) => {
	return new Promise((resolve, reject) => {
		const img = new Image();
		img.onload = () => resolve(img);
		img.onerror = reject;
		img.src = src;
	});
};

const canvasToBlob = (canvas, type, quality = 0.92) => {
	return new Promise((resolve, reject) => {
		canvas.toBlob((blob) => {
			if (!blob) {
				reject(new Error("No fue posible generar la imagen rotada."));
				return;
			}
			resolve(blob);
		}, type, quality);
	});
};

const rotateImage = async (degrees) => {
	if (!(value.value instanceof File)) {
		return;
	}

	isProcessing.value = true;

	try {
		const currentFile = value.value;
		const imageUrl = URL.createObjectURL(currentFile);

		try {
			const img = await loadImage(imageUrl);
			const normalizedDegrees = ((degrees % 360) + 360) % 360;

			const canvas = document.createElement("canvas");
			const ctx = canvas.getContext("2d");

			if (!ctx) {
				throw new Error("No fue posible inicializar el canvas.");
			}

			const isQuarterTurn = normalizedDegrees === 90 || normalizedDegrees === 270;

			canvas.width = isQuarterTurn ? img.height : img.width;
			canvas.height = isQuarterTurn ? img.width : img.height;

			ctx.translate(canvas.width / 2, canvas.height / 2);
			ctx.rotate((normalizedDegrees * Math.PI) / 180);
			ctx.drawImage(img, -img.width / 2, -img.height / 2);

			const outputType = currentFile.type || "image/png";
			const rotatedBlob = await canvasToBlob(canvas, outputType);

			const rotatedFile = new File(
				[rotatedBlob],
				currentFile.name,
				{
					type: outputType,
					lastModified: Date.now(),
				}
			);

			handleChange(rotatedFile);
		} finally {
			URL.revokeObjectURL(imageUrl);
		}
	} catch (error) {
		console.error("Error al rotar la imagen:", error);
	} finally {
		isProcessing.value = false;
	}
};

onBeforeUnmount(() => {
	revokePreviewUrl();
});
</script>

<style scoped>
.preview-wrapper {
	display: flex;
	justify-content: center;
	align-items: center;
	overflow: hidden;
}

.profile-preview {
	max-width: 100%;
	max-height: 300px;
	object-fit: contain;
	display: block;
	cursor: pointer;
}
</style>