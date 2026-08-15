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
      
      <!-- Page Header & Main CTA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Create, manage, and customize dynamic CRUD modules for your application</p>
        </div>

        <Link
          href="/administrator/modules/step1"
          class="px-5 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-2 shadow-lg transition-transform hover:scale-105 active:scale-95 self-start sm:self-auto"
          style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
        >
          <i class="bi bi-cpu-fill text-sm"></i>
          <span>Generate New Module</span>
        </Link>
      </div>

      <!-- Quick Metrics Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-boxes"></i>
          </div>
          <div>
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Custom Modules</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ modules ? modules.length : 0 }}</h3>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-database-check"></i>
          </div>
          <div>
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Database Tables</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ tables ? tables.length : 0 }}</h3>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-shield-check"></i>
          </div>
          <div>
            <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">System Security</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">Active</h3>
          </div>
        </div>
      </div>

      <!-- Modules Table Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl shadow-slate-200/40 dark:shadow-none overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
          <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Generated Module Registry</h2>
          <span class="text-xs font-semibold text-slate-400">{{ modules ? modules.length : 0 }} registered modules</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                <th class="py-3.5 px-4">ID</th>
                <th class="py-3.5 px-4">Module Name</th>
                <th class="py-3.5 px-4">Table Name</th>
                <th class="py-3.5 px-4">Path Slug</th>
                <th class="py-3.5 px-4">Controller</th>
                <th class="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs font-medium">
              <tr v-for="item in modules" :key="item.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition duration-150">
                <td class="py-3.5 px-4">
                  <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-lg border border-indigo-200/40 dark:border-indigo-800/40">
                    #{{ item.id }}
                  </span>
                </td>
                
                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/80 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 border border-indigo-100 dark:border-indigo-900">
                      <i :class="[item.icon || 'bi bi-boxes', 'text-sm']"></i>
                    </div>
                    <span>{{ item.name }}</span>
                  </div>
                </td>

                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">{{ item.table_name }}</td>
                
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 font-mono text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700">
                    /{{ item.path }}
                  </span>
                </td>

                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ item.controller }}</td>
                
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <Link
                      :href="'/administrator/modules/step1/' + item.id"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/80 font-bold text-xs rounded-xl border border-indigo-200/50 dark:border-indigo-800/50 transition"
                    >
                      <i class="bi bi-pencil-square text-xs"></i>
                      <span>Module Wizard</span>
                    </Link>

                    <button
                      @click="deleteModule(item.id, item.name)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                      title="Delete Module"
                    >
                      <i class="bi bi-trash3 text-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!modules || !modules.length">
                <td colspan="6" class="p-12 text-center text-slate-400 dark:text-slate-500">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xl">
                      <i class="bi bi-cpu"></i>
                    </div>
                    <p class="font-bold text-sm text-slate-700 dark:text-slate-300">No custom modules generated yet</p>
                    <p class="text-xs text-slate-400">Click "Generate New Module" above to start building!</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
