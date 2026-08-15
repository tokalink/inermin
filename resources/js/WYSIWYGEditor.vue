<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Type content here...',
  },
  disabled: Boolean,
  readonly: Boolean,
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
const isHtmlMode = ref(false)
const rawHtml = ref(props.modelValue || '')

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue || ''
  }
})

watch(
  () => props.modelValue,
  (newVal) => {
    rawHtml.value = newVal || ''
    if (editorRef.value && editorRef.value.innerHTML !== newVal) {
      editorRef.value.innerHTML = newVal || ''
    }
  }
)

const handleInput = () => {
  if (editorRef.value) {
    const html = editorRef.value.innerHTML
    rawHtml.value = html
    emit('update:modelValue', html)
  }
}

const handleRawInput = (e) => {
  const val = e.target.value
  rawHtml.value = val
  if (editorRef.value) {
    editorRef.value.innerHTML = val
  }
  emit('update:modelValue', val)
}

const exec = (cmd, arg = null) => {
  if (props.disabled || props.readonly || isHtmlMode.value) return
  document.execCommand(cmd, false, arg)
  handleInput()
}

const addLink = () => {
  if (props.disabled || props.readonly || isHtmlMode.value) return
  const url = prompt('Enter URL link:', 'https://')
  if (url) {
    exec('createLink', url)
  }
}
</script>

<template>
  <div class="rounded-2xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] overflow-hidden shadow-xs">
    
    <!-- Toolbar -->
    <div class="p-2 bg-stone-50 dark:bg-white/[0.02] border-b border-stone-200 dark:border-white/5 flex flex-wrap items-center gap-1 text-xs">
      
      <!-- Text Style -->
      <button
        type="button"
        @click="exec('bold')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Bold (Ctrl+B)"
      >
        <i class="bi bi-type-bold"></i>
      </button>

      <button
        type="button"
        @click="exec('italic')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Italic (Ctrl+I)"
      >
        <i class="bi bi-type-italic"></i>
      </button>

      <button
        type="button"
        @click="exec('underline')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Underline (Ctrl+U)"
      >
        <i class="bi bi-type-underline"></i>
      </button>

      <button
        type="button"
        @click="exec('strikeThrough')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Strikethrough"
      >
        <i class="bi bi-type-strikethrough"></i>
      </button>

      <div class="w-px h-4 bg-stone-300 dark:bg-white/10 mx-1"></div>

      <!-- Headings -->
      <button
        type="button"
        @click="exec('formatBlock', '<h2>')"
        :disabled="disabled || readonly || isHtmlMode"
        class="px-2 py-1 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold text-[11px] disabled:opacity-40"
        title="Heading 2"
      >
        H2
      </button>

      <button
        type="button"
        @click="exec('formatBlock', '<h3>')"
        :disabled="disabled || readonly || isHtmlMode"
        class="px-2 py-1 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold text-[11px] disabled:opacity-40"
        title="Heading 3"
      >
        H3
      </button>

      <div class="w-px h-4 bg-stone-300 dark:bg-white/10 mx-1"></div>

      <!-- Lists -->
      <button
        type="button"
        @click="exec('insertUnorderedList')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Bullet List"
      >
        <i class="bi bi-list-ul"></i>
      </button>

      <button
        type="button"
        @click="exec('insertOrderedList')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Numbered List"
      >
        <i class="bi bi-list-ol"></i>
      </button>

      <!-- Link -->
      <button
        type="button"
        @click="addLink"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Insert Link"
      >
        <i class="bi bi-link-45deg"></i>
      </button>

      <div class="w-px h-4 bg-stone-300 dark:bg-white/10 mx-1"></div>

      <!-- Clear Formatting -->
      <button
        type="button"
        @click="exec('removeFormat')"
        :disabled="disabled || readonly || isHtmlMode"
        class="p-1.5 rounded-lg text-stone-700 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-white/10 transition font-bold disabled:opacity-40"
        title="Clear Formatting"
      >
        <i class="bi bi-eraser-fill"></i>
      </button>

      <!-- Toggle HTML View -->
      <button
        type="button"
        @click="isHtmlMode = !isHtmlMode"
        :class="[
          'px-2 py-1 rounded-lg text-[10px] font-mono font-bold transition ml-auto border',
          isHtmlMode ? 'bg-[rgb(var(--accent-rgb))] text-white border-transparent' : 'border-stone-300 dark:border-white/10 text-stone-600 dark:text-stone-300'
        ]"
      >
        {{ isHtmlMode ? '</> Visual' : '</> HTML' }}
      </button>

    </div>

    <!-- Visual Content Editable Area -->
    <div
      v-show="!isHtmlMode"
      ref="editorRef"
      contenteditable="true"
      @input="handleInput"
      :class="[
        'p-4 min-h-[140px] max-h-[400px] overflow-y-auto text-xs text-stone-900 dark:text-white focus:outline-none leading-relaxed prose dark:prose-invert max-w-none',
        (disabled || readonly) ? 'bg-stone-100 dark:bg-white/5 cursor-not-allowed opacity-60' : ''
      ]"
    ></div>

    <!-- HTML Source Code View -->
    <textarea
      v-show="isHtmlMode"
      :value="rawHtml"
      @input="handleRawInput"
      rows="6"
      class="w-full p-4 font-mono text-xs text-stone-900 dark:text-white bg-stone-900 dark:bg-[#0c0b09] text-amber-400 focus:outline-none"
    ></textarea>

  </div>
</template>
