<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  apps: {
    type: Array,
    default: () => [],
  },
  currentApp: {
    type: String,
    default: 'core',
  },
})

const isOpen = ref(false)
const page = usePage()

const defaultApps = computed(() => {
  if (props.apps && props.apps.length > 0) {
    return props.apps
  }
  return [
    { name: 'Core Admin', code: 'core', icon: 'bi bi-grid-fill', active: true },
    { name: 'Mutasi Suite', code: 'mutasi', icon: 'bi bi-arrow-repeat', active: true },
    { name: 'CRM Suite', code: 'crm', icon: 'bi bi-people-fill', active: true },
    { name: 'Invoicing & Billing', code: 'invoicing', icon: 'bi bi-receipt-cutoff', active: true },
    { name: 'HR & Absensi', code: 'hr', icon: 'bi bi-clock-history', active: true },
  ]
})

const selectApp = (appCode) => {
  isOpen.value = false
  const adminPath = '/' + (page.props.admin_path || 'administrator').replace(/^\//, '')
  if (appCode === 'core') {
    router.get(adminPath)
  } else {
    router.get('/' + appCode)
  }
}
</script>

<template>
  <div class="relative">
    <!-- App Switcher Trigger Button (Google Workspace Style Icon ::) -->
    <button
      type="button"
      @click="isOpen = !isOpen"
      :class="[
        'w-9 h-9 rounded-xl flex items-center justify-center text-sm transition-all duration-200 border',
        isOpen
          ? 'bg-amber-500/20 text-amber-500 border-amber-500/40 shadow-lg shadow-amber-500/20 scale-105'
          : 'bg-stone-100 dark:bg-white/5 text-stone-600 dark:text-stone-300 border-stone-200 dark:border-white/10 hover:bg-stone-200 dark:hover:bg-white/10'
      ]"
      title="App Switcher Launcher"
    >
      <i class="bi bi-grid-3x3-gap-fill text-base"></i>
    </button>

    <!-- App Launcher Modal Dropdown -->
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="absolute left-0 mt-3 w-80 bg-[#15130f] border border-white/10 rounded-3xl p-5 shadow-2xl z-50 space-y-4 backdrop-blur-2xl"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-white/5 pb-3">
          <div class="flex items-center gap-2">
            <i class="bi bi-box-seam-fill text-amber-500 text-base"></i>
            <span class="font-display font-bold text-xs text-white tracking-wider uppercase">Application Suites</span>
          </div>

        </div>

        <!-- Apps Grid -->
        <div class="grid grid-cols-2 gap-2.5">
          <button
            v-for="app in defaultApps"
            :key="app.code"
            @click="selectApp(app.code)"
            :class="[
              'p-3.5 rounded-2xl flex flex-col items-center justify-center text-center gap-2 transition-all duration-200 border group',
              currentApp === app.code || (currentApp === '' && app.code === 'core')
                ? 'bg-amber-500/15 border-amber-500/40 text-amber-400 shadow-lg shadow-amber-500/10'
                : 'bg-white/[0.02] border-white/5 text-stone-300 hover:bg-white/5 hover:border-white/10 hover:text-white'
            ]"
          >
            <div :class="[
              'w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold transition group-hover:scale-110',
              currentApp === app.code ? 'bg-amber-500 text-stone-950 shadow-md shadow-amber-500/30' : 'bg-white/5 text-stone-300 group-hover:text-amber-400'
            ]">
              <i :class="app.icon || 'bi bi-app-indicator'"></i>
            </div>
            <span class="text-[11px] font-bold tracking-tight line-clamp-1">{{ app.name }}</span>
          </button>
        </div>

        <!-- Upgrade / Manage Subscriptions Link -->
        <div class="pt-2 border-t border-white/5">
          <Link
            :href="'/' + (page.props.admin_path || 'administrator') + '/apps'"
            @click="isOpen = false"
            class="w-full py-2 rounded-xl bg-white/5 hover:bg-amber-500/20 hover:text-amber-400 text-stone-400 font-bold text-[11px] flex items-center justify-center gap-2 transition"
          >
            <i class="bi bi-plus-circle text-xs"></i>
            <span>Manage App Suites</span>
          </Link>
        </div>

      </div>
    </Transition>
  </div>
</template>
