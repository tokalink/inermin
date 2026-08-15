<script setup>
import InerminAppLayout from '../Layouts/InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  stats: Object,
  user: Object,
})
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6">
      
      <!-- Welcome Header Card -->
      <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xl shadow-indigo-600/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Welcome back, {{ user.name }}! 👋</h1>
          <p class="text-indigo-100 text-xs sm:text-sm mt-1">You are logged in as <span class="font-semibold underline decoration-indigo-300">{{ user.privilege }}</span>.</p>
        </div>
        <button class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wide transition self-start sm:self-auto">
          System Healthy
        </button>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <!-- Stat Card 1 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-people"></i>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Users</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">{{ stats.users }}</h3>
          </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-boxes"></i>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Active Modules</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">{{ stats.modules }}</h3>
          </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-journal-text"></i>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">User Access Logs</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">{{ stats.logs }}</h3>
          </div>
        </div>

      </div>

    </div>
  </InerminAppLayout>
</template>
