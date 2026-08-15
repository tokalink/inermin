<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  form_schema: Array,
  row: Object,
  back_url: String,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

const resolvedBackUrl = computed(() => {
  return props.back_url || currentPath.value.replace(/\/detail\/.*/, '')
})

const isImage = (val) => {
  if (typeof val !== 'string') return false
  return val.match(/\.(jpeg|jpg|gif|png|webp|svg)/i) != null || val.startsWith('storage/') || val.startsWith('http')
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
        <div v-for="field in form_schema" :key="field.name" class="border-b border-slate-100 dark:border-slate-800/60 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <span class="text-xs font-bold text-slate-500 dark:text-slate-400 w-1/3">{{ field.label }}</span>
          
          <div class="w-2/3 text-xs text-slate-800 dark:text-slate-100 font-medium">
            <!-- Image preview -->
            <div v-if="isImage(row[field.name])" class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
              <img :src="row[field.name].startsWith('http') ? row[field.name] : '/' + row[field.name]" class="w-full h-full object-cover" alt="Detail Image" />
            </div>

            <span v-else>{{ row[field.name] !== null && row[field.name] !== undefined ? row[field.name] : '-' }}</span>
          </div>
        </div>

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
