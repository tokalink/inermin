<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  privileges: Array,
  stats: Object,
  activities: Array,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator'))

const searchQuery = ref('')

const filteredPrivileges = computed(() => {
  if (!searchQuery.value.trim()) return props.privileges || []
  const q = searchQuery.value.toLowerCase()
  return (props.privileges || []).filter(p => 
    p.name.toLowerCase().includes(q)
  )
})
</script>

<template>
  <InerminAppLayout>
    <Head :title="page_title || 'Privileges & Roles — Aether Console'" />

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold tracking-[0.18em] text-stone-400 mb-2">
          <span class="w-6 h-px bg-stone-300 dark:bg-stone-600"></span>
          ADMINISTRATION
        </div>
        <h1 class="font-display text-3xl lg:text-4xl font-bold tracking-tight text-stone-900 dark:text-white">
          Privileges & Roles
        </h1>
        <p class="text-stone-500 dark:text-stone-400 text-sm mt-2 max-w-xl">
          Define, assign, and audit role-based access across every module. Changes propagate instantly to all active sessions.
        </p>
      </div>

      <div class="flex items-center gap-2.5">
        <a
          :href="adminPath + '/privileges?export=1'"
          class="px-4 py-2.5 rounded-xl text-xs font-bold border border-stone-200 dark:border-white/10 hover:bg-stone-100 dark:hover:bg-white/5 flex items-center gap-2 transition"
        >
          <i class="bi bi-download"></i>
          <span>Export Roles</span>
        </a>

        <Link
          :href="adminPath + '/privileges/add'"
          class="px-5 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-2 shadow-lg transition-transform hover:scale-105"
          style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
        >
          <i class="bi bi-plus-lg text-sm"></i>
          <span>Create Role</span>
        </Link>
      </div>
    </div>

    <!-- Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      
      <!-- Stat Card 1 -->
      <div class="card rounded-2xl p-5 hover:border-[rgba(var(--accent-rgb),0.35)] transition-all">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-[11px] tracking-[0.15em] text-stone-400 font-semibold uppercase">TOTAL ROLES</div>
            <div class="font-display text-3xl font-bold mt-2 text-stone-900 dark:text-white">
              {{ stats?.total_roles || privileges?.length || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
            <i class="bi bi-shield-check text-xl"></i>
          </div>
        </div>
        <div class="flex items-center gap-1.5 mt-3 text-xs">
          <span class="flex items-center gap-0.5 text-emerald-600 dark:text-emerald-400 font-semibold">
            <i class="bi bi-graph-up-arrow"></i> Active
          </span>
          <span class="text-stone-400">across system</span>
        </div>
      </div>

      <!-- Stat Card 2 -->
      <div class="card rounded-2xl p-5 hover:border-[rgba(var(--accent-rgb),0.35)] transition-all">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-[11px] tracking-[0.15em] text-stone-400 font-semibold uppercase">ASSIGNED USERS</div>
            <div class="font-display text-3xl font-bold mt-2 text-stone-900 dark:text-white">
              {{ stats?.total_users || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
            <i class="bi bi-people text-xl"></i>
          </div>
        </div>
        <div class="flex items-center gap-1.5 mt-3 text-xs">
          <span class="text-stone-400">Registered accounts</span>
        </div>
      </div>

      <!-- Stat Card 3 -->
      <div class="card rounded-2xl p-5 hover:border-[rgba(var(--accent-rgb),0.35)] transition-all">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-[11px] tracking-[0.15em] text-stone-400 font-semibold uppercase">PERMISSIONS</div>
            <div class="font-display text-3xl font-bold mt-2 text-stone-900 dark:text-white">
              {{ stats?.total_modules || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
            <i class="bi bi-key text-xl"></i>
          </div>
        </div>
        <div class="flex items-center gap-1.5 mt-3 text-xs">
          <span class="text-stone-400">Protected modules</span>
        </div>
      </div>

      <!-- Stat Card 4 -->
      <div class="card rounded-2xl p-5 hover:border-[rgba(var(--accent-rgb),0.35)] transition-all">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-[11px] tracking-[0.15em] text-stone-400 font-semibold uppercase">RECENT LOGS</div>
            <div class="font-display text-3xl font-bold mt-2 text-stone-900 dark:text-white">
              {{ stats?.recent_logs || 0 }}
            </div>
          </div>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
            <i class="bi bi-clock-history text-xl"></i>
          </div>
        </div>
        <div class="flex items-center gap-1.5 mt-3 text-xs">
          <span class="flex items-center gap-0.5 font-semibold" style="color: rgb(var(--accent-rgb));">
            <i class="bi bi-dot"></i> Live tracking
          </span>
        </div>
      </div>

    </div>

    <!-- Permissions Matrix & Role Management Table Card -->
    <div class="card rounded-2xl overflow-hidden mb-6 shadow-sm">
      
      <!-- Table Header Bar -->
      <div class="p-5 border-b border-stone-200 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-display font-semibold text-base text-stone-900 dark:text-white">
            Role Matrix & Privileges
          </h3>
          <p class="text-xs text-stone-400 mt-0.5">Manage roles, accent theme colors, and module permissions</p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Filter Search -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Filter roles..."
              class="w-48 bg-stone-100 dark:bg-white/5 rounded-xl pl-9 pr-3 py-2 text-xs text-stone-900 dark:text-stone-100 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition"
            />
            <i class="bi bi-funnel absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-xs"></i>
          </div>
        </div>
      </div>

      <!-- Table Body -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[10px] tracking-[0.15em] text-stone-400 font-semibold border-b border-stone-200 dark:border-white/5 uppercase bg-stone-50/50 dark:bg-white/[0.02]">
              <th class="px-6 py-3.5">ROLE NAME</th>
              <th class="px-4 py-3.5 text-center">TYPE</th>
              <th class="px-4 py-3.5 text-center">ACCENT COLOR</th>
              <th class="px-4 py-3.5 text-center">USERS COUNT</th>
              <th class="px-6 py-3.5 text-right">ACTIONS</th>
            </tr>
          </thead>
          
          <tbody class="divide-y divide-stone-100 dark:divide-white/5">
            <template v-if="filteredPrivileges && filteredPrivileges.length">
              <tr
                v-for="p in filteredPrivileges"
                :key="p.id"
                class="hover:bg-stone-50/80 dark:hover:bg-white/[0.02] transition-colors"
              >
                <!-- Role Name & Initials Badge -->
                <td class="px-6 py-4 font-bold text-stone-900 dark:text-white">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-display font-bold text-sm shadow-sm"
                         style="background: rgba(var(--accent-rgb), 0.12); color: rgb(var(--accent-rgb));">
                      {{ p.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="text-xs font-bold text-stone-900 dark:text-white">{{ p.name }}</div>
                      <div class="text-[10px] text-stone-400 font-medium font-mono">ID #{{ p.id }}</div>
                    </div>
                  </div>
                </td>

                <!-- Superadmin Type Tag -->
                <td class="px-4 py-4 text-center">
                  <span
                    v-if="p.is_superadmin"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20"
                  >
                    Superadmin
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-stone-100 dark:bg-white/10 text-stone-600 dark:text-stone-300"
                  >
                    Standard Role
                  </span>
                </td>

                <!-- Accent Color Badge -->
                <td class="px-4 py-4 text-center">
                  <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-xl bg-stone-100 dark:bg-white/5 border border-stone-200/60 dark:border-white/5 text-[11px] font-semibold text-stone-600 dark:text-stone-300">
                    <span class="w-3 h-3 rounded-full" :style="`background: var(--accent-soft)`"></span>
                    <span>{{ p.theme_color || 'theme-indigo' }}</span>
                  </div>
                </td>

                <!-- Users Count -->
                <td class="px-4 py-4 text-center font-bold text-xs text-stone-700 dark:text-stone-300">
                  {{ p.users_count || 0 }} Users
                </td>

                <!-- Action Buttons -->
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="adminPath + '/privileges/edit/' + p.id"
                      class="px-3 py-1.5 rounded-xl border border-stone-200 dark:border-white/10 hover:bg-stone-100 dark:hover:bg-white/5 text-stone-700 dark:text-stone-200 text-xs font-bold transition flex items-center gap-1.5"
                    >
                      <i class="bi bi-pencil"></i>
                      <span>Configure Permissions</span>
                    </Link>

                    <Link
                      v-if="p.id !== 1"
                      :href="adminPath + '/privileges/delete/' + p.id"
                      method="post"
                      as="button"
                      confirm="Are you sure you want to delete this privilege?"
                      class="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                      title="Delete Privilege"
                    >
                      <i class="bi bi-trash text-sm"></i>
                    </Link>
                  </div>
                </td>
              </tr>
            </template>

            <tr v-else>
              <td colspan="5" class="px-6 py-12 text-center text-stone-400 font-medium">
                No privileges found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </InerminAppLayout>
</template>
