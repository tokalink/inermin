<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  form_schema: Array,
  forms: Array,
  row: Object,
  back_url: String,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

const schema = computed(() => props.form_schema || props.forms || [])

const resolvedBackUrl = computed(() => {
  return props.back_url || currentPath.value.replace(/\/detail\/.*/, '')
})

const isImage = (val) => {
  if (typeof val !== 'string') return false
  return val.match(/\.(jpeg|jpg|gif|png|webp|svg)/i) != null || val.startsWith('storage/') || val.startsWith('http')
}

// Option normalization for selects / radios / checkboxes / datatable options
const getOptions = (field) => {
  if (!field.dataenum) return []
  if (Array.isArray(field.dataenum)) {
    return field.dataenum.map(opt => {
      if (typeof opt === 'object' && opt !== null) {
        return { value: opt.value !== undefined ? opt.value : opt.id, label: opt.label || opt.name || opt.value }
      }
      return { value: opt, label: opt }
    })
  }
  if (typeof field.dataenum === 'object') {
    return Object.entries(field.dataenum).map(([val, label]) => ({ value: val, label: label }))
  }
  return []
}

// Helper to resolve display value for a field (supporting Joins & dataenum mappings)
const getFieldValue = (field) => {
  if (!field || !field.name || !props.row) return '-'

  const fieldName = field.name
  const possibleAliases = [
    `${fieldName}_label`,
    fieldName.endsWith('_id') ? `${fieldName.replace(/_id$/, '')}_label` : `${fieldName}_id_label`,
  ]

  // 1. Check if direct row has joined label (e.g. user_id_label or user_label)
  for (const alias of possibleAliases) {
    if (props.row[alias] !== undefined && props.row[alias] !== null && props.row[alias] !== '') {
      return props.row[alias]
    }
  }

  const rawVal = props.row[fieldName]

  if (rawVal === null || rawVal === undefined || rawVal === '') return '-'

  // 2. Check if field has dataenum (options array or key-value object)
  if (field.dataenum) {
    const options = getOptions(field)
    const matched = options.find(opt => String(opt.value) === String(rawVal))
    if (matched) {
      return matched.label
    }
  }

  return rawVal
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-4xl mx-auto space-y-6 font-sans">
      
      <!-- Detail Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Viewing detailed record information</p>
        </div>

        <Link
          :href="resolvedBackUrl"
          class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 font-semibold text-xs rounded-xl shadow-sm transition"
        >
          <i class="bi bi-arrow-left text-sm"></i>
          <span>Back</span>
        </Link>
      </div>

      <!-- Detail Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden p-6 space-y-4">
        <template v-for="field in schema" :key="field.name || field.label">
          <!-- Ignore hidden fields or section headers if any -->
          <div v-if="field.type === 'header' || field.type === 'heading'" class="border-b border-slate-200 dark:border-slate-800 pt-3 pb-1">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ field.label }}</h3>
          </div>

          <div v-else-if="field.type !== 'hidden'" class="border-b border-slate-100 dark:border-slate-800/60 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 w-1/3">{{ field.label }}</span>
            
            <div class="w-2/3 text-xs text-slate-800 dark:text-slate-100 font-medium">
              <!-- Image preview -->
              <div v-if="isImage(getFieldValue(field))" class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                <img :src="String(getFieldValue(field)).startsWith('http') ? getFieldValue(field) : '/' + getFieldValue(field)" class="w-full h-full object-cover" alt="Detail Image" />
              </div>

              <span v-else>{{ getFieldValue(field) }}</span>
            </div>
          </div>
        </template>

        <div class="pt-2 flex justify-end">
          <Link
            :href="resolvedBackUrl"
            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition"
          >
            Back to List
          </Link>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
