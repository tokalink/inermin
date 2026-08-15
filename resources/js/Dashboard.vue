<script setup>
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  stats: Object,
  user: Object,
})
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Welcome Header Banner -->
      <div class="p-6 sm:p-8 rounded-3xl bg-indigo-600 text-white shadow-xl shadow-indigo-600/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Welcome back, {{ user.name }}! 👋</h1>
          <p class="text-indigo-100 text-xs sm:text-sm font-medium mt-1.5">You are logged in as <span class="font-bold underline decoration-indigo-300">{{ user.privilege }}</span>.</p>
        </div>
        <button class="px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/20 text-white text-xs font-bold tracking-wide transition self-start sm:self-auto">
          System Healthy
        </button>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        
        <!-- Stat Card 1 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0">
            <i class="bi bi-people"></i>
          </div>
          <div>
            <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Users</p>
            <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.users }}</h3>
          </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl shrink-0">
            <i class="bi bi-boxes"></i>
          </div>
          <div>
            <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Modules</p>
            <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.modules }}</h3>
          </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition flex items-center gap-4">
          <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shrink-0">
            <i class="bi bi-journal-text"></i>
          </div>
          <div>
            <p class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User Access Logs</p>
            <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.logs }}</h3>
          </div>
        </div>

      </div>

    </div>
  </InerminAppLayout>
</template>
