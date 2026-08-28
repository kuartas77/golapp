<template>
	<div class="upload text-center" :class="`upload--${previewSize}`">
		<input ref="fl_profile" type="file" class="d-none" :id="name" :accept="accept" @change="onFileChange" />

		<div class="preview-wrapper">
			<img :src="preview ? preview : defaultPreview" :alt="alt" class="profile-preview"
				:style="{ maxHeight: computedPreviewHeight }"
				@click="$refs.fl_profile.click()" />
		</div>

		<div class="mt-2 d-flex justify-content-center gap-2 flex-wrap">
			<button type="button" class="btn btn-sm btn-outline-primary" @click="$refs.fl_profile.click()">
				<i class="fa fa-upload fa-width-auto" aria-hidden="true"></i>
				<span>{{ preview ? 'Cambiar' : 'Cargar' }}</span>
			</button>

			<button type="button" class="btn btn-sm btn-outline-secondary" @click="rotateImage(90)"
				:disabled="isProcessing || !canRotate">
				<i class="fa fa-rotate-right fa-width-auto" aria-hidden="true"></i>
			</button>

			<button type="button" class="btn btn-sm btn-outline-secondary" @click="rotateImage(-90)"
				:disabled="isProcessing || !canRotate">
				<i class="fa fa-rotate-left fa-width-auto" aria-hidden="true"></i>
			</button>

			<button v-if="allowRemove && preview" type="button" class="btn btn-sm btn-outline-danger" @click="removeImage">
				<i class="fa fa-trash fa-width-auto" aria-hidden="true"></i>
				<span>Quitar</span>
			</button>
		</div>

		<p class="mt-2" v-if="label">
			<label :for="name" class="form-label">{{ label }}</label>
		</p>
	</div>

	<small v-if="hint" class="form-text text-muted d-block text-center mt-1">{{ hint }}</small>
	<div :class="fieldInvalid ? 'custom-error' : ''">{{ displayedError }}</div>
</template>
<script setup>
import { ref, watch, onBeforeUnmount, computed } from "vue";
import { useField } from "vee-validate";

const props = defineProps({
	name: { type: String, required: true },
	modelValue: { type: [File, String, null], default: null },
	label: { type: String, default: "Selecciona un archivo" },
	accept: { type: String, default: "image/png, image/jpeg, image/webp" },
	defaultPreview: { type: String, default: "/img/ball-dark.webp" },
	alt: { type: String, default: "Imagen seleccionada" },
	previewSize: {
		type: String,
		default: "default",
		validator: (value) => ["default", "large"].includes(value),
	},
	previewMaxHeight: { type: [Number, String], default: null },
	maxSizeMb: { type: Number, default: 5 },
	allowRemove: { type: Boolean, default: false },
	standalone: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "removed"]);
const { value, meta, errorMessage, handleChange, setValue } = useField(props.name);

const preview = ref(null);
const isProcessing = ref(false);
const initialPreviewUrl = ref(null);
const localError = ref("");

let previewObjectUrl = null;
const currentValue = computed(() => props.standalone ? props.modelValue : value.value);
const canRotate = computed(() => Boolean(preview.value) && preview.value !== props.defaultPreview);
const computedPreviewHeight = computed(() => {
	const height = props.previewMaxHeight ?? (props.previewSize === "large" ? 520 : 300);
	return typeof height === "number" ? `${height}px` : height;
});
const fieldInvalid = computed(() => Boolean(localError.value) || (!props.standalone && !meta.valid));
const displayedError = computed(() => localError.value || (props.standalone ? "" : errorMessage.value));
const hint = computed(() => `PNG, JPG o WebP. Máximo ${props.maxSizeMb} MB.`);

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
	localError.value = "";

	if (fileOrPath instanceof File) {
		previewObjectUrl = URL.createObjectURL(fileOrPath);
		preview.value = previewObjectUrl;
		return;
	}

	if (isUrlPreview(fileOrPath)) {
		initialPreviewUrl.value = fileOrPath;
		preview.value = fileOrPath;

		// Limpia el valor real del field para no enviar la URL al backend
		if (!props.standalone && value.value !== null) {
			setValue('');
		}
		return;
	}

	preview.value = initialPreviewUrl.value || null;
};

watch(currentValue, (file) => {
	setPreviewFromValue(file);
}, { immediate: true });

const onFileChange = (e) => {
	const file = e.target.files?.[0];

	if (!file) {
		return;
	}

	setFile(file);
};

const setFile = (file) => {
	if (!isAcceptedImage(file)) {
		localError.value = "El archivo debe ser una imagen PNG, JPG o WebP.";
		clearNativeInput();
		return;
	}

	if (file.size > props.maxSizeMb * 1024 * 1024) {
		localError.value = `La imagen no puede superar ${props.maxSizeMb} MB.`;
		clearNativeInput();
		return;
	}

	localError.value = "";

	if (props.standalone) {
		emit("update:modelValue", file);
		return;
	}

	handleChange(file);
};

const loadImage = (src) => {
	return new Promise((resolve, reject) => {
		const img = new Image();
		img.onload = () => resolve(img);
		img.onerror = reject;
		if (/^https?:\/\//.test(src)) {
			img.crossOrigin = "anonymous";
		}
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
	if (!preview.value) {
		return;
	}

	isProcessing.value = true;

	try {
		const sourceValue = currentValue.value;
		const currentFile = sourceValue instanceof File ? sourceValue : null;
		const imageUrl = currentFile ? URL.createObjectURL(currentFile) : preview.value;

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

			const outputType = currentFile?.type || "image/jpeg";
			const rotatedBlob = await canvasToBlob(canvas, outputType);

			const rotatedFile = new File(
				[rotatedBlob],
				currentFile?.name || "imagen-fase-rotada.jpg",
				{
					type: outputType,
					lastModified: Date.now(),
				}
			);

			setFile(rotatedFile);
		} finally {
			if (currentFile) {
				URL.revokeObjectURL(imageUrl);
			}
		}
	} catch (error) {
		console.error("Error al rotar la imagen:", error);
		localError.value = "No fue posible rotar la imagen.";
	} finally {
		isProcessing.value = false;
	}
};

const removeImage = () => {
	revokePreviewUrl();
	preview.value = null;
	initialPreviewUrl.value = null;
	localError.value = "";
	clearNativeInput();

	if (props.standalone) {
		emit("update:modelValue", null);
	} else {
		setValue(null);
	}

	emit("removed");
};

const clearNativeInput = () => {
	const input = document.getElementById(props.name);

	if (input) {
		input.value = "";
	}
};

const isAcceptedImage = (file) => ["image/jpeg", "image/png", "image/webp"].includes(file.type);

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

.upload--large .preview-wrapper {
	min-height: 220px;
}

.upload--large .profile-preview {
	width: 100%;
}
</style>
