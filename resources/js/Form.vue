<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'
import LOVModal from './LOVModal.vue'
import WYSIWYGEditor from './WYSIWYGEditor.vue'

const props = defineProps({
  page_title: String,
  form_schema: Array,
  forms: Array,
  action_url: String,
  row: Object,
  is_edit: Boolean,
  is_detail: Boolean,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

// Safely normalize form schema
const schema = computed(() => props.form_schema || props.forms || [])

// Initialize Form State
const initialData = {}
schema.value.forEach(field => {
  if (field.name) {
    initialData[field.name] = props.row && props.row[field.name] !== undefined 
      ? props.row[field.name] 
      : (field.default !== undefined ? field.default : '')
  }
})

const form = useForm(initialData)
const previews = ref({})
const isDragging = ref({})

// LOV State
const showLovModal = ref(false)
const activeLovField = ref(null)
const lovDisplayLabels = ref({})

onMounted(() => {
  // Initialize initial LOV labels from row object if present
  schema.value.forEach((field) => {
    if (field.type === 'lov' && props.row) {
      if (props.row[field.name + '_label']) {
        lovDisplayLabels.value[field.name] = props.row[field.name + '_label']
      }
    }
  })
})

const openLovModal = (field) => {
  activeLovField.value = field
  showLovModal.value = true
}

const handleLovSelect = (selectedRow) => {
  if (!activeLovField.value || !selectedRow) return
  const field = activeLovField.value
  const valKey = field.lov_value || 'id'
  const labelKey = field.lov_label || 'name'

  form[field.name] = selectedRow[valKey]
  lovDisplayLabels.value[field.name] = selectedRow[labelKey] || selectedRow[valKey]

  // Handle LOV Autofill Mapping
  if (field.lov_autofill && typeof field.lov_autofill === 'object') {
    Object.entries(field.lov_autofill).forEach(([srcCol, targetFieldName]) => {
      if (selectedRow[srcCol] !== undefined && form[targetFieldName] !== undefined) {
        form[targetFieldName] = selectedRow[srcCol]
      }
    })
  }
}

const clearLov = (fieldName) => {
  form[fieldName] = ''
  delete lovDisplayLabels.value[fieldName]
}

// File Upload Handler
const handleFileChange = (e, fieldName) => {
  const file = e.target.files ? e.target.files[0] : (e.dataTransfer ? e.dataTransfer.files[0] : null)
  if (file) {
    form[fieldName] = file
    if (file.type && file.type.startsWith('image/')) {
      previews.value[fieldName] = URL.createObjectURL(file)
    } else {
      previews.value[fieldName] = file.name
    }
  }
}

const handleDrop = (e, fieldName) => {
  isDragging.value[fieldName] = false
  handleFileChange(e, fieldName)
}

const clearFile = (fieldName) => {
  form[fieldName] = ''
  delete previews.value[fieldName]
}

// Option normalization for selects / radios / checkboxes
const getOptions = (field) => {
  if (!field.dataenum) return []
  if (Array.isArray(field.dataenum)) {
    return field.dataenum.map(opt => {
      if (typeof opt === 'object' && opt !== null) {
        return { value: opt.value || opt.id, label: opt.label || opt.name || opt.value }
      }
      return { value: opt, label: opt }
    })
  }
  if (typeof field.dataenum === 'object') {
    return Object.entries(field.dataenum).map(([val, label]) => ({ value: val, label: label }))
  }
  return []
}

// Form Submission
const submitForm = () => {
  form.post(props.action_url, {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-5xl mx-auto space-y-6 font-sans w-full antialiased">
      
      <!-- Form Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-stone-400">
            <Link :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')" class="hover:text-stone-200">
              Module List
            </Link>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-[rgb(var(--accent-rgb))] font-bold">{{ is_detail ? 'Detail Record' : (is_edit ? 'Edit Record' : 'Create Record') }}</span>
          </div>

          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 dark:text-white mt-1">
            {{ page_title }}
          </h1>
          <p class="text-xs text-stone-400 font-medium mt-0.5">Please complete the required form inputs below</p>
        </div>

        <Link
          :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')"
          class="px-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-white/5 font-bold text-xs shadow-xs transition flex items-center gap-2 self-start sm:self-auto"
        >
          <i class="bi bi-arrow-left text-sm"></i>
          <span>Back to Registry</span>
        </Link>
      </div>

      <!-- Form Container Card (#15130f Obsidian Card) -->
      <div class="card rounded-3xl border border-stone-200 dark:border-white/5 shadow-2xl overflow-hidden">
        
        <form @submit.prevent="submitForm" class="p-6 lg:p-8 space-y-6">
          
          <!-- Dynamic Form Grid (Supports multi-column field layouts) -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            <template v-for="field in schema" :key="field.name || field.label">
              
              <!-- SECTION HEADER DIVIDER TYPE -->
              <div v-if="field.type === 'header' || field.type === 'heading'" class="col-span-full pt-4 border-b border-stone-200 dark:border-white/5 pb-2">
                <h3 class="font-display text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
                  <i :class="[field.icon || 'bi bi-grid-fill', 'text-[rgb(var(--accent-rgb))]']"></i>
                  {{ field.label }}
                </h3>
                <p v-if="field.help" class="text-xs text-stone-400 font-medium mt-0.5">{{ field.help }}</p>
              </div>

              <!-- HIDDEN FIELD TYPE -->
              <input v-else-if="field.type === 'hidden'" type="hidden" v-model="form[field.name]" />

              <!-- STANDARD FIELD WRAPPER -->
              <div
                v-else
                :class="[
                  'space-y-2',
                  field.width ? field.width : (field.type === 'textarea' || field.type === 'wysiwyg' || field.type === 'upload' || field.type === 'image' ? 'col-span-12' : 'col-span-12 md:col-span-6')
                ]"
              >
                <!-- Label & Requirement Badge -->
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                    {{ field.label }}
                    <span v-if="field.required" class="text-rose-500 font-extrabold ml-0.5">*</span>
                  </label>

                  <span v-if="field.type === 'number' || field.type === 'money'" class="text-[10px] font-mono text-stone-400">Numeric</span>
                </div>

                <!-- 1. TEXT / EMAIL / PASSWORD / NUMBER / MONEY / CURRENCY -->
                <div v-if="!field.type || ['text', 'email', 'password', 'number', 'money', 'currency'].includes(field.type)" class="relative flex items-center">
                  <span v-if="field.type === 'money' || field.type === 'currency'" class="absolute left-3.5 text-xs font-bold text-stone-400 font-mono pointer-events-none">
                    Rp
                  </span>

                  <input
                    v-model="form[field.name]"
                    :type="field.type === 'money' || field.type === 'currency' ? 'number' : (field.type || 'text')"
                    :placeholder="field.placeholder || 'Enter ' + field.label"
                    :required="field.required"
                    :disabled="is_detail || field.readonly || field.disabled"
                    :class="[
                      'w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl py-2.5 text-xs text-stone-900 dark:text-white placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition',
                      field.type === 'money' || field.type === 'currency' ? 'pl-9 pr-3.5 font-mono' : 'px-3.5'
                    ]"
                  />
                </div>

                <!-- 2. DATE / TIME / DATETIME -->
                <div v-else-if="['date', 'time', 'datetime', 'datetime-local'].includes(field.type)" class="relative flex items-center">
                  <input
                    v-model="form[field.name]"
                    :type="field.type === 'datetime' ? 'datetime-local' : field.type"
                    :required="field.required"
                    :disabled="is_detail || field.readonly || field.disabled"
                    class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition font-mono custom-date-input"
                  />
                </div>

                <!-- 3. TEXTAREA / WYSIWYG / CKEDITOR -->
                <div v-else-if="['wysiwyg', 'ckeditor', 'tinymce', 'richtext', 'html'].includes(field.type)">
                  <WYSIWYGEditor
                    v-model="form[field.name]"
                    :placeholder="field.placeholder || 'Enter ' + field.label"
                    :height="field.height || '200px'"
                    :disabled="is_detail || field.readonly || field.disabled"
                    :readonly="is_detail || field.readonly || field.disabled"
                  />
                </div>

                <div v-else-if="field.type === 'textarea'">
                  <textarea
                    v-model="form[field.name]"
                    rows="4"
                    :placeholder="field.placeholder || 'Enter ' + field.label"
                    :required="field.required"
                    :disabled="is_detail || field.readonly || field.disabled"
                    class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition"
                  ></textarea>
                </div>

                <!-- 4. SELECT / SELECT2 -->
                <div v-else-if="field.type === 'select' || field.type === 'select2'" class="relative flex items-center">
                  <select
                    v-model="form[field.name]"
                    :required="field.required"
                    :disabled="is_detail || field.readonly || field.disabled"
                    class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl pl-3.5 pr-10 py-2.5 text-xs text-stone-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition appearance-none cursor-pointer"
                  >
                    <option value="" class="bg-white dark:bg-[#15130f] text-stone-900 dark:text-white">-- Select {{ field.label }} --</option>
                    <option
                      v-for="opt in getOptions(field)"
                      :key="opt.value"
                      :value="opt.value"
                      class="bg-white dark:bg-[#15130f] text-stone-900 dark:text-white"
                    >
                      {{ opt.label }}
                    </option>
                  </select>
                  <i class="bi bi-chevron-down absolute right-3.5 text-stone-500 dark:text-stone-400 text-xs pointer-events-none"></i>
                </div>

                <!-- 5. RADIO BUTTONS -->
                <div v-else-if="field.type === 'radio'" class="flex flex-wrap gap-3 pt-1">
                  <label
                    v-for="opt in getOptions(field)"
                    :key="opt.value"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-stone-200 dark:border-white/10 bg-stone-50 dark:bg-white/5 cursor-pointer text-xs font-bold text-stone-800 dark:text-stone-200 hover:border-[rgb(var(--accent-rgb))] transition"
                  >
                    <input
                      type="radio"
                      v-model="form[field.name]"
                      :value="opt.value"
                      :disabled="is_detail || field.readonly || field.disabled"
                      class="text-[rgb(var(--accent-rgb))] focus:ring-0"
                    />
                    <span>{{ opt.label }}</span>
                  </label>
                </div>

                <!-- 6. UPLOAD / FILE / IMAGE DROPZONE -->
                <div v-else-if="['upload', 'file', 'image', 'upload_image', 'upload_standard'].includes(field.type)" class="space-y-3">
                  <div
                    @dragover.prevent="isDragging[field.name] = true"
                    @dragleave.prevent="isDragging[field.name] = false"
                    @drop.prevent="handleDrop($event, field.name)"
                    :class="[
                      'relative border-2 border-dashed rounded-2xl p-4 text-center transition-all duration-200 cursor-pointer flex flex-col items-center justify-center space-y-2',
                      isDragging[field.name]
                        ? 'border-[rgb(var(--accent-rgb))] bg-[rgba(var(--accent-rgb),0.08)]'
                        : 'border-stone-200 dark:border-white/10 bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5'
                    ]"
                  >
                    <input
                      type="file"
                      @change="(e) => handleFileChange(e, field.name)"
                      class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />

                    <!-- Preview Thumbnail if Image -->
                    <div v-if="previews[field.name] || form[field.name]" class="relative group">
                      <img
                        v-if="previews[field.name] || typeof form[field.name] === 'string'"
                        :src="previews[field.name] || (typeof form[field.name] === 'string' ? (form[field.name].startsWith('http') || form[field.name].startsWith('/') ? form[field.name] : '/' + form[field.name]) : '')"
                        class="w-24 h-24 object-cover rounded-2xl shadow-md border-2 border-[rgb(var(--accent-rgb))]"
                        alt="Preview"
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <span v-else class="text-xs font-mono font-bold text-stone-700 dark:text-stone-300">
                        {{ form[field.name]?.name || form[field.name] }}
                      </span>

                      <button
                        type="button"
                        @click.stop="clearFile(field.name)"
                        class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs shadow-md hover:scale-110 transition"
                        title="Remove file"
                      >
                        <i class="bi bi-x"></i>
                      </button>
                    </div>

                    <div v-else class="space-y-1">
                      <div class="w-10 h-10 rounded-2xl mx-auto flex items-center justify-center text-lg" style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                      </div>
                      <p class="text-xs font-bold text-stone-800 dark:text-stone-200">
                        Click to upload or drag & drop file
                      </p>
                      <p class="text-[10px] text-stone-400">PNG, JPG, WEBP, PDF or DOCX</p>
                    </div>
                  </div>
                </div>

                <!-- 7. COLOR PICKER -->
                <div v-else-if="field.type === 'color' || field.type === 'colorpicker'" class="flex items-center gap-3">
                  <input
                    type="color"
                    v-model="form[field.name]"
                    class="w-10 h-10 rounded-xl border border-stone-200 dark:border-white/10 bg-transparent cursor-pointer"
                  />
                  <input
                    type="text"
                    v-model="form[field.name]"
                    class="flex-1 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white font-mono uppercase"
                  />
                </div>

                <!-- 8. LIST OF VALUES (LOV) PICKER -->
                <div v-else-if="field.type === 'lov'" class="relative flex items-center gap-2">
                  <input
                    type="text"
                    readonly
                    :value="lovDisplayLabels[field.name] || (row && row[field.name + '_label'] ? row[field.name + '_label'] : form[field.name])"
                    :placeholder="field.placeholder || 'Click browse to select ' + field.label"
                    :disabled="is_detail || field.readonly || field.disabled"
                    @click="openLovModal(field)"
                    class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white font-medium cursor-pointer placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition"
                  />
                  
                  <button
                    type="button"
                    :disabled="is_detail || field.readonly || field.disabled"
                    @click="openLovModal(field)"
                    class="px-3.5 py-2.5 rounded-2xl text-xs font-bold text-white shadow-md transition shrink-0 flex items-center gap-1.5 disabled:opacity-50"
                    style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
                  >
                    <i class="bi bi-search"></i>
                    <span>Browse</span>
                  </button>

                  <button
                    v-if="form[field.name] && !is_detail && !field.readonly && !field.disabled"
                    type="button"
                    @click="clearLov(field.name)"
                    class="px-3 py-2.5 rounded-2xl text-xs font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200/50 dark:border-rose-800/40 transition shrink-0"
                    title="Clear LOV"
                  >
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

                <!-- Helper Text -->
                <p v-if="field.help" class="text-[11px] text-stone-400 font-medium flex items-center gap-1 mt-1">
                  <i class="bi bi-info-circle text-[10px]"></i>
                  <span>{{ field.help }}</span>
                </p>

                <!-- Validation Error Message -->
                <p v-if="form.errors[field.name]" class="text-[11px] text-rose-500 font-bold flex items-center gap-1 mt-1">
                  <i class="bi bi-exclamation-circle-fill"></i>
                  <span>{{ form.errors[field.name] }}</span>
                </p>

              </div>

            </template>

          </div>

          <!-- Form Actions Footer -->
          <div class="pt-6 border-t border-stone-200 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <Link
              :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '').replace(/\/detail\/.*/, '')"
              class="px-5 py-2.5 bg-stone-100 dark:bg-white/5 text-stone-700 dark:text-stone-300 font-bold text-xs rounded-xl hover:bg-stone-200 dark:hover:bg-white/10 transition self-stretch sm:self-auto text-center"
            >
              {{ is_detail ? 'Back' : 'Cancel' }}
            </Link>

            <div v-if="!is_detail" class="flex items-center gap-3 w-full sm:w-auto">
              <button
                type="submit"
                :disabled="form.processing"
                class="flex-1 sm:flex-none px-7 py-3 rounded-xl text-xs font-bold text-white flex items-center justify-center gap-2 shadow-lg transition-transform hover:scale-105 active:scale-95 disabled:opacity-50"
                style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
              >
                <i class="bi bi-floppy-fill text-sm"></i>
                <span>{{ is_edit ? 'Update Record' : 'Save Record' }}</span>
              </button>
            </div>
          </div>

        </form>

      </div>

      <!-- LOV Lookup Modal Dialog Component -->
      <LOVModal
        v-if="activeLovField"
        :show="showLovModal"
        :title="'Select ' + (activeLovField.label || 'Value')"
        :table="activeLovField.lov_table"
        :value-column="activeLovField.lov_value || 'id'"
        :label-column="activeLovField.lov_label || 'name'"
        :columns="activeLovField.lov_columns || (activeLovField.lov_label || 'name')"
        :where="activeLovField.lov_where || ''"
        @close="showLovModal = false"
        @select="handleLovSelect"
      />

    </div>
  </InerminAppLayout>
</template>

<style>
/* Color scheme adaptation for native date/time pickers */
input[type="date"],
input[type="time"],
input[type="datetime-local"] {
  color-scheme: light;
}

.dark input[type="date"],
.dark input[type="time"],
.dark input[type="datetime-local"] {
  color-scheme: dark;
}
</style>
