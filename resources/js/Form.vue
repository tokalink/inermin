<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  form_schema: Array,
  action_url: String,
  row: Object,
  is_edit: Boolean,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

const initialData = {}
props.form_schema.forEach(field => {
  initialData[field.name] = props.row ? props.row[field.name] : (field.default || '')
})

const form = useForm(initialData)
const previews = ref({})

const handleFileChange = (e, fieldName) => {
  const file = e.target.files[0]
  if (file) {
    form[fieldName] = file
    previews.value[fieldName] = URL.createObjectURL(file)
  }
}

const submit = () => {
  form.post(props.action_url, {
    forceFormData: true,
  })
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-4xl mx-auto space-y-6 font-sans">
      
      <!-- Form Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Fill out the form details below</p>
        </div>

        <Link
          :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')"
          class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 font-semibold text-xs rounded-xl shadow-sm transition"
        >
          <i class="bi bi-arrow-left text-sm"></i>
          <span>Back</span>
        </Link>
      </div>

      <!-- Form Container Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <form @submit.prevent="submit" class="p-6 space-y-5">
          
          <div v-for="field in form_schema" :key="field.name" class="space-y-1.5">
            
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
              {{ field.label }}
              <span v-if="field.required" class="text-rose-500">*</span>
            </label>

            <!-- Standard Text / Email / Password / Number / Date -->
            <input
              v-if="!field.type || ['text', 'email', 'password', 'number', 'date', 'datetime-local', 'time'].includes(field.type)"
              v-model="form[field.name]"
              :type="field.type === 'datetime' ? 'datetime-local' : (field.type || 'text')"
              :placeholder="field.placeholder || 'Enter ' + field.label"
              :required="field.required"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
            />

            <!-- Textarea -->
            <textarea
              v-else-if="field.type === 'textarea' || field.type === 'wysiwyg'"
              v-model="form[field.name]"
              rows="4"
              :placeholder="field.placeholder || 'Enter ' + field.label"
              :required="field.required"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
            ></textarea>

            <!-- Select / Select2 -->
            <select
              v-else-if="field.type === 'select' || field.type === 'select2'"
              v-model="form[field.name]"
              :required="field.required"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
            >
              <option value="">-- Select {{ field.label }} --</option>
              <template v-if="Array.isArray(field.dataenum)">
                <option v-for="opt in field.dataenum" :key="opt" :value="opt">{{ opt }}</option>
              </template>
              <template v-else-if="typeof field.dataenum === 'object'">
                <option v-for="(val, key) in field.dataenum" :key="key" :value="key">{{ val }}</option>
              </template>
            </select>

            <!-- Upload / Image Dropzone -->
            <div v-else-if="['upload', 'file', 'image', 'upload_standard', 'upload_image'].includes(field.type)" class="space-y-2">
              <div class="flex items-center gap-4">
                <div v-if="previews[field.name] || form[field.name]" class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0">
                  <img :src="previews[field.name] || (typeof form[field.name] === 'string' ? '/' + form[field.name] : '')" class="w-full h-full object-cover" alt="Preview" />
                </div>

                <input
                  type="file"
                  @change="(e) => handleFileChange(e, field.name)"
                  class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 dark:file:bg-indigo-950 dark:file:text-indigo-400 hover:file:bg-indigo-100 cursor-pointer"
                />
              </div>
            </div>

            <!-- Helper Text -->
            <p v-if="field.help" class="text-[11px] text-slate-400 dark:text-slate-500">{{ field.help }}</p>

            <!-- Error Alert -->
            <p v-if="form.errors[field.name]" class="text-[11px] text-rose-500 font-semibold">{{ form.errors[field.name] }}</p>

          </div>

          <!-- Form Actions -->
          <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
            <Link
              :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')"
              class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition"
            >
              Cancel
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 disabled:opacity-50 transition flex items-center gap-2"
            >
              <i class="bi bi-floppy"></i>
              <span>{{ is_edit ? 'Update Data' : 'Save Data' }}</span>
            </button>
          </div>

        </form>
      </div>

    </div>
  </InerminAppLayout>
</template>
