<template>
    <div class="tinymce-editor">
        <textarea
            :id="editorId"
            ref="textareaRef"
            :value="modelValue"
            class="form-control tinymce-editor__textarea"
        ></textarea>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    allowPageBreak: {
        type: Boolean,
        default: false,
    },
    minHeight: {
        type: Number,
        default: 220,
    },
})

const emit = defineEmits(['update:modelValue'])

const textareaRef = ref(null)
const editorInstance = ref(null)
const editorId = `tinymce-editor-${Math.random().toString(36).slice(2, 11)}`

let tinyMceLoaderPromise = globalThis.__tinymceLoaderPromise ?? null

const ensureTinyMce = () => {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('TinyMCE solo esta disponible en el navegador.'))
    }

    if (window.tinymce) {
        return Promise.resolve(window.tinymce)
    }

    if (!tinyMceLoaderPromise) {
        tinyMceLoaderPromise = new Promise((resolve, reject) => {
            const existingScript = document.querySelector('script[data-tinymce-loader="true"]')

            if (existingScript) {
                existingScript.addEventListener('load', () => resolve(window.tinymce), { once: true })
                existingScript.addEventListener('error', reject, { once: true })
                return
            }

            const script = document.createElement('script')
            script.src = '/js/tinymce/tinymce.min.js'
            script.async = true
            script.defer = true
            script.dataset.tinymceLoader = 'true'
            script.addEventListener('load', () => resolve(window.tinymce), { once: true })
            script.addEventListener('error', reject, { once: true })
            document.head.appendChild(script)
        })

        globalThis.__tinymceLoaderPromise = tinyMceLoaderPromise
    }

    return tinyMceLoaderPromise
}

const applyDisabledMode = (disabled) => {
    if (!editorInstance.value) {
        return
    }

    editorInstance.value.setMode(disabled ? 'readonly' : 'design')
}

const initEditor = async () => {
    const tinymce = await ensureTinyMce()

    const plugins = [
        'quickbars',
        'code',
        'table',
        'lists',
        'image',
        'wordcount',
        'searchreplace',
    ]

    if (props.allowPageBreak) {
        plugins.push('pagebreak')
    }

    const editors = await tinymce.init({
        target: textareaRef.value,
        menubar: false,
        branding: false,
        min_height: props.minHeight,
        plugins: plugins.join(' '),
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table image',
        language: 'es_MX',
        content_css: ['/css/dompdf.css', '/css/dompdf-overrides.css'],
        entity_encoding: 'raw',
        extended_valid_elements: '+*[*]',
        pagebreak_separator: '<pagebreak />',
        indent: true,
        br_in_pre: true,
        setup: (editor) => {
            editor.on('init', () => {
                editor.setContent(props.modelValue || '')
                applyDisabledMode(props.disabled)
            })

            editor.on('change input undo redo', () => {
                emit('update:modelValue', editor.getContent())
            })
        },
    })

    editorInstance.value = Array.isArray(editors) ? editors[0] : editors
}

onMounted(async () => {
    try {
        await initEditor()
    } catch (error) {
        console.error(error)
    }
})

onBeforeUnmount(() => {
    if (editorInstance.value) {
        window.tinymce?.remove?.(editorInstance.value)
        editorInstance.value = null
    }
})

watch(
    () => props.modelValue,
    (value) => {
        const editor = editorInstance.value

        if (!editor) {
            if (textareaRef.value) {
                textareaRef.value.value = value || ''
            }
            return
        }

        if (editor.getContent() !== (value || '')) {
            editor.setContent(value || '')
        }
    }
)

watch(
    () => props.disabled,
    (disabled) => {
        applyDisabledMode(disabled)
    }
)
</script>

<style scoped>
.tinymce-editor__textarea {
    min-height: 160px;
}
</style>
