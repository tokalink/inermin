<script setup>
import { Link } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  modules: Array,
  tables: Array,
})

const deleteModule = (id, name) => {
  if (confirm(`Are you sure you want to delete module "${name}"?`)) {
    window.location.href = `/administrator/modules/delete/${id}`
  }
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage and generate dynamic CRUDBooster modules</p>
        </div>

        <Link
          href="/administrator/modules/step1"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition self-start sm:self-auto"
        >
          <i class="bi bi-plus-lg text-sm"></i>
          <span>Generate New Module</span>
        </Link>
      </div>

      <!-- Modules Table Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/40 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                <th class="p-3.5">ID</th>
                <th class="p-3.5">Module Name</th>
                <th class="p-3.5">Table Name</th>
                <th class="p-3.5">Path Slug</th>
                <th class="p-3.5">Controller</th>
                <th class="p-3.5 text-right">Actions</th>
              </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
              <tr v-for="item in modules" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition">
                <td class="p-3.5 font-semibold text-slate-400">{{ item.id }}</td>
                <td class="p-3.5 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                  <i :class="[item.icon || 'bi bi-boxes', 'text-indigo-600 dark:text-indigo-400 text-base']"></i>
                  <span>{{ item.name }}</span>
                </td>
                <td class="p-3.5 text-slate-600 dark:text-slate-300 font-mono">{{ item.table_name }}</td>
                <td class="p-3.5">
                  <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 font-mono text-[11px] rounded-lg border border-slate-200 dark:border-slate-700">
                    /{{ item.path }}
                  </span>
                </td>
                <td class="p-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ item.controller }}</td>
                <td class="p-3.5 text-right">
                  <div class="inline-flex items-center gap-2">
                    <Link
                      :href="'/administrator/modules/step1/' + item.id"
                      class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-semibold text-xs rounded-lg transition"
                    >
                      <i class="bi bi-magic"></i>
                      <span>Module Wizard</span>
                    </Link>

                    <button
                      @click="deleteModule(item.id, item.name)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                      title="Delete Module"
                    >
                      <i class="bi bi-trash text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!modules || !modules.length">
                <td colspan="6" class="p-8 text-center text-slate-400 dark:text-slate-500">
                  <i class="bi bi-boxes text-3xl block mb-2 opacity-50"></i>
                  No custom modules generated yet. Click "Generate New Module" to get started!
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
