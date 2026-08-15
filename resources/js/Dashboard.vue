<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  stats: Object,
  user: Object,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Welcome Header Banner (Dynamic Accent Gradient) -->
      <div
        class="p-6 sm:p-8 rounded-3xl text-white shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-300"
        style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 12px 36px -10px rgba(var(--accent-rgb), 0.45);"
      >
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider mb-2">
            <span>AETHER CONSOLE DASHBOARD</span>
          </div>
          <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Welcome back, {{ user.name }}! 👋
          </h1>
          <p class="text-white/80 text-xs sm:text-sm font-medium mt-1">
            Logged in as <span class="font-bold text-white underline decoration-white/40">{{ user.privilege }}</span>. System is running smoothly.
          </p>
        </div>

        <div class="flex items-center gap-3 self-start sm:self-auto">
          <Link
            :href="adminPath + '/modules/step1'"
            class="px-4 py-2.5 rounded-xl bg-white text-stone-900 hover:bg-stone-100 font-bold text-xs shadow-md transition flex items-center gap-2"
          >
            <i class="bi bi-cpu-fill"></i>
            <span>+ Create Module</span>
          </Link>

          <div class="px-4 py-2.5 rounded-xl bg-black/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>System Active</span>
          </div>
        </div>
      </div>

      <!-- 4 Stat Cards Grid (Matching d1.html) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Users -->
        <div class="card stat-card p-5 rounded-2xl shadow-sm flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">TOTAL USERS</p>
            <h3 class="font-display text-3xl font-extrabold text-stone-900 dark:text-white mt-1">{{ stats.users || 0 }}</h3>
            <p class="text-[11px] font-bold text-emerald-500 flex items-center gap-1 mt-1">
              <i class="bi bi-graph-up-arrow"></i>
              <span>Active users logged</span>
            </p>
          </div>
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background: rgba(var(--accent-rgb), 0.14); color: rgb(var(--accent-rgb));">
            <i class="bi bi-people-fill"></i>
          </div>
        </div>

        <!-- Card 2: Modules -->
        <div class="card stat-card p-5 rounded-2xl shadow-sm flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">ACTIVE MODULES</p>
            <h3 class="font-display text-3xl font-extrabold text-stone-900 dark:text-white mt-1">{{ stats.modules || 0 }}</h3>
            <p class="text-[11px] font-bold text-emerald-500 flex items-center gap-1 mt-1">
              <i class="bi bi-boxes"></i>
              <span>Generated CRUDs</span>
            </p>
          </div>
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background: rgba(var(--accent-rgb), 0.14); color: rgb(var(--accent-rgb));">
            <i class="bi bi-box-seam-fill"></i>
          </div>
        </div>

        <!-- Card 3: Security & Roles -->
        <div class="card stat-card p-5 rounded-2xl shadow-sm flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">ROLE PERMISSIONS</p>
            <h3 class="font-display text-3xl font-extrabold text-stone-900 dark:text-white mt-1">Active</h3>
            <p class="text-[11px] font-bold text-stone-400 flex items-center gap-1 mt-1">
              <i class="bi bi-shield-check"></i>
              <span>RBAC matrix enforced</span>
            </p>
          </div>
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background: rgba(var(--accent-rgb), 0.14); color: rgb(var(--accent-rgb));">
            <i class="bi bi-shield-lock-fill"></i>
          </div>
        </div>

        <!-- Card 4: Access Logs -->
        <div class="card stat-card p-5 rounded-2xl shadow-sm flex items-center justify-between">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">USER ACCESS LOGS</p>
            <h3 class="font-display text-3xl font-extrabold text-stone-900 dark:text-white mt-1">{{ stats.logs || 0 }}</h3>
            <p class="text-[11px] font-bold text-amber-500 flex items-center gap-1 mt-1">
              <i class="bi bi-clock-history"></i>
              <span>Recorded sessions</span>
            </p>
          </div>
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background: rgba(var(--accent-rgb), 0.14); color: rgb(var(--accent-rgb));">
            <i class="bi bi-journal-text"></i>
          </div>
        </div>

      </div>

      <!-- Main Dashboard Grid (Quick Actions & System Overview matching d1.html) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Quick Management Shortcuts -->
        <div class="lg:col-span-2 space-y-6">
          <div class="card rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-display text-base font-bold text-stone-900 dark:text-white">Quick Administration Console</h3>
                <p class="text-xs text-stone-400 mt-0.5">Direct shortcuts to manage application system core</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <Link
                :href="adminPath + '/modules'"
                class="p-4 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5 transition group flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                  <i class="bi bi-code-square"></i>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition">Module Generator</h4>
                  <p class="text-[11px] text-stone-400">Generate new CRUDs</p>
                </div>
              </Link>

              <Link
                :href="adminPath + '/privileges'"
                class="p-4 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5 transition group flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                  <i class="bi bi-shield-check"></i>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition">Privileges & Roles</h4>
                  <p class="text-[11px] text-stone-400">RBAC permissions matrix</p>
                </div>
              </Link>

              <Link
                :href="adminPath + '/users'"
                class="p-4 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5 transition group flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                  <i class="bi bi-people"></i>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition">Users Management</h4>
                  <p class="text-[11px] text-stone-400">User accounts & roles</p>
                </div>
              </Link>

              <Link
                :href="adminPath + '/logs'"
                class="p-4 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5 transition group flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 group-hover:scale-110 transition" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                  <i class="bi bi-journal-text"></i>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition">Log User Access</h4>
                  <p class="text-[11px] text-stone-400">Audit trail logs</p>
                </div>
              </Link>
            </div>
          </div>
        </div>

        <!-- Right 1 Col: System Info Card -->
        <div>
          <div class="card rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-display text-base font-bold text-stone-900 dark:text-white">Aether System Engine</h3>
            
            <div class="space-y-3 text-xs font-medium">
              <div class="flex items-center justify-between p-3 rounded-xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200 dark:border-white/5">
                <span class="text-stone-400">Environment</span>
                <span class="font-bold text-stone-900 dark:text-white">Laravel SPA (Inertia + Vue 3)</span>
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200 dark:border-white/5">
                <span class="text-stone-400">Design System</span>
                <span class="font-bold text-stone-900 dark:text-white">Aether Console (#0c0b09)</span>
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200 dark:border-white/5">
                <span class="text-stone-400">FastExcel Stream</span>
                <span class="font-bold text-emerald-500 flex items-center gap-1">
                  <i class="bi bi-check-circle-fill"></i> Enabled
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </InerminAppLayout>
</template>
