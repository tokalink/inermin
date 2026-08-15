<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const appName = computed(() => page.props.app_name || 'Inermin Admin')
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator'))
const user = computed(() => page.props.auth?.user || {})
const menu = computed(() => page.props.menu || [])
const flash = computed(() => page.props.flash || {})

// System Superadmin Built-in Menus
const systemMenus = computed(() => [
  { name: 'Privileges Roles', path: adminPath.value + '/privileges', icon: 'bi bi-key-fill' },
  { name: 'Users Management', path: adminPath.value + '/users', icon: 'bi bi-people-fill' },
  { name: 'Menu Management', path: adminPath.value + '/menus', icon: 'bi bi-list-nested' },
  { name: 'Settings', path: adminPath.value + '/settings', icon: 'bi bi-gear-fill' },
  { name: 'Module Generator', path: adminPath.value + '/modules', icon: 'bi bi-boxes' },
  { name: 'Statistic Builder', path: adminPath.value + '/statistic_builder', icon: 'bi bi-graph-up-arrow' },
  { name: 'API Generator', path: adminPath.value + '/api_generator', icon: 'bi bi-code-slash' },
  { name: 'Email Templates', path: adminPath.value + '/email_templates', icon: 'bi bi-envelope-at' },
  { name: 'Log User Access', path: adminPath.value + '/logs', icon: 'bi bi-journal-text' },
])

// Dark/Light Mode State
const isDark = ref(true)

const toggleTheme = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('inermin_theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('inermin_theme', 'light')
  }
}

// Collapsible Sidebar State (Desktop & Mobile)
const isCollapsed = ref(false)
const isMobileOpen = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const toggleMobileMenu = () => {
  isMobileOpen.value = !isMobileOpen.value
}

// User Profile Menu Dropdown
const isUserDropdownOpen = ref(false)

onMounted(() => {
  const savedTheme = localStorage.getItem('inermin_theme') || page.props.default_theme || 'dark'
  isDark.value = savedTheme === 'dark'
  if (isDark.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-300 font-sans flex">
    
    <!-- Mobile Sidebar Backdrop -->
    <div
      v-if="isMobileOpen"
      @click="isMobileOpen = false"
      class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity"
    ></div>

    <!-- Sidebar Drawer (Desktop Collapsible & Mobile Slide-over) -->
    <aside
      :class="[
        'fixed lg:static inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800/80 shadow-lg lg:shadow-none transition-all duration-300',
        isCollapsed ? 'lg:w-20' : 'lg:w-64',
        isMobileOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Sidebar Header (Brand Logo & Name) -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200 dark:border-slate-800/80">
        <Link :href="adminPath" class="flex items-center gap-3 overflow-hidden">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20 shrink-0">
            I
          </div>
          <span v-if="!isCollapsed || isMobileOpen" class="font-bold text-lg tracking-tight text-slate-900 dark:text-white truncate">
            {{ appName }}
          </span>
        </Link>

        <!-- Desktop Collapse Button -->
        <button
          @click="toggleSidebar"
          class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition"
          title="Toggle Sidebar Collapse"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform" :class="isCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>
      </div>

      <!-- Sidebar Navigation Links -->
      <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar">
        
        <!-- Dashboard Link -->
        <Link
          :href="adminPath"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm transition-all',
            $page.url === adminPath || $page.url === adminPath + '/'
              ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
              : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-indigo-600 dark:hover:text-white'
          ]"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          <span v-if="!isCollapsed || isMobileOpen" class="truncate">Dashboard</span>
        </Link>

        <!-- Custom User Menu Items -->
        <template v-for="item in menu" :key="item.id">
          
          <!-- Submenu Dropdown if children exist -->
          <div v-if="item.children && item.children.length" class="space-y-1">
            <div class="flex items-center justify-between px-3 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
              <div class="flex items-center gap-2">
                <i :class="[item.icon || 'bi bi-folder', 'text-base shrink-0']"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ item.name }}</span>
              </div>
            </div>

            <div class="pl-3 space-y-1">
              <Link
                v-for="child in item.children"
                :key="child.id"
                :href="child.url || (adminPath + '/' + child.path)"
                :class="[
                  'flex items-center gap-3 px-3 py-2 rounded-xl font-medium text-xs transition-all',
                  $page.url === child.url || (child.url && child.url !== '#' && $page.url.startsWith(child.url))
                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                    : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-indigo-600 dark:hover:text-white'
                ]"
              >
                <i :class="[child.icon || 'bi bi-grid', 'text-sm shrink-0']"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ child.name }}</span>
              </Link>
            </div>
          </div>

          <!-- Single Menu Item -->
          <Link
            v-else
            :href="item.url || (adminPath + '/' + item.path)"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-sm transition-all',
              $page.url === item.url || (item.url && item.url !== '#' && $page.url.startsWith(item.url))
                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-indigo-600 dark:hover:text-white'
            ]"
          >
            <i :class="[item.icon || 'bi bi-grid', 'text-lg shrink-0']"></i>
            <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ item.name }}</span>
          </Link>

        </template>


        <!-- System Administration Section Header -->
        <div v-if="user.is_superadmin" class="pt-4 pb-1">
          <p v-if="!isCollapsed || isMobileOpen" class="px-3 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">
            SUPERADMIN
          </p>
          <div v-else class="h-px bg-slate-200 dark:bg-slate-800 my-2"></div>
        </div>

        <!-- Built-in Superadmin System Menus -->
        <template v-if="user.is_superadmin" v-for="sysMenu in systemMenus" :key="sysMenu.path">
          <Link
            :href="sysMenu.path"
            :class="[
              'flex items-center gap-3 px-3 py-2 rounded-xl font-medium text-sm transition-all',
              $page.url.startsWith(sysMenu.path)
                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-indigo-600 dark:hover:text-white'
            ]"
          >
            <i :class="[sysMenu.icon, 'text-base shrink-0']"></i>
            <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ sysMenu.name }}</span>
          </Link>
        </template>


      </div>

      <!-- Sidebar User Footer -->
      <div class="p-3 border-t border-slate-200 dark:border-slate-800/80">
        <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-100 dark:bg-slate-800/40">
          <img :src="user.photo" class="w-9 h-9 rounded-lg object-cover ring-2 ring-indigo-500/30 shrink-0" alt="Avatar" />
          <div v-if="!isCollapsed || isMobileOpen" class="overflow-hidden leading-tight flex-1">
            <h4 class="font-semibold text-xs text-slate-900 dark:text-slate-100 truncate">{{ user.name }}</h4>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ user.privilege_name }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Shell -->
    <div class="flex-1 flex flex-col min-w-0">
      
      <!-- Topbar Header -->
      <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
        
        <!-- Left Mobile Menu Toggle Button -->
        <div class="flex items-center gap-3">
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <!-- Right Utilities & Profile Dropdown -->
        <div class="flex items-center gap-3">
          
          <!-- Dark / Light Mode Switcher Toggle Button -->
          <button
            @click="toggleTheme"
            class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- User Menu Dropdown -->
          <div class="relative">
            <button
              @click="isUserDropdownOpen = !isUserDropdownOpen"
              class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
              <img :src="user.photo" class="w-8 h-8 rounded-lg object-cover ring-2 ring-indigo-500/20" alt="Avatar" />
              <span class="hidden sm:inline font-semibold text-xs text-slate-700 dark:text-slate-200">{{ user.name }}</span>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="isUserDropdownOpen"
              @click="isUserDropdownOpen = false"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-1.5 z-50"
            >
              <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ user.name }}</p>
                <p class="text-[10px] text-slate-400">{{ user.privilege_name }}</p>
              </div>
              <Link :href="adminPath + '/logout'" class="w-full flex items-center gap-2 px-4 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
              </Link>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Page Content Area -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
        
        <!-- Flash Alert Messages -->
        <div v-if="flash.success" class="mb-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
          <span>{{ flash.success }}</span>
        </div>
        <div v-if="flash.error || flash.message" class="mb-4 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold flex items-center justify-between">
          <span>{{ flash.error || flash.message }}</span>
        </div>

        <slot />
      </main>


    </div>
  </div>
</template>
