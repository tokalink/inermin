<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppSwitcher from './Components/AppSwitcher.vue'

const page = usePage()

const appName = computed(() => page.props.app_name || 'Aether Console')
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))
const user = computed(() => page.props.auth?.user || {})
const menu = computed(() => page.props.menu || [])
const flash = computed(() => page.props.flash || {})
const rawNotifications = computed(() => page.props.notifications || [])

// Notification State & Actions
const isNotificationsOpen = ref(false)
const notificationItems = ref([])

watch(rawNotifications, (newVal) => {
  notificationItems.value = (newVal || []).map(item => ({
    ...item,
    is_read: item.is_read || 0
  }))
}, { immediate: true })

const unreadCount = computed(() => {
  return notificationItems.value.filter(n => !n.is_read).length
})

const markAllAsRead = () => {
  notificationItems.value.forEach(n => n.is_read = 1)
}

const toggleNotification = (item) => {
  item.is_read = 1
}

// Grouped Superadmin Built-in Menus (Matching d1.html)
const adminGroupMenus = computed(() => [
  { name: 'Privileges Roles', path: adminPath.value + '/privileges', icon: 'bi bi-shield-check' },
  { name: 'Users Management', path: adminPath.value + '/users', icon: 'bi bi-people' },
  { name: 'Menu Management', path: adminPath.value + '/menus', icon: 'bi bi-grid-3x3-gap' },
])

const devToolsGroupMenus = computed(() => [
  { name: 'Module Generator', path: adminPath.value + '/modules', icon: 'bi bi-code-square', badge: 'NEW' },
  { name: 'API Generator', path: adminPath.value + '/api_generator', icon: 'bi bi-plugin' },
  { name: 'Statistic Builder', path: adminPath.value + '/statistic_builder', icon: 'bi bi-bar-chart-line' },
])

const systemGroupMenus = computed(() => [
  { name: 'Email Templates', path: adminPath.value + '/email_templates', icon: 'bi bi-envelope' },
  { name: 'Settings', path: adminPath.value + '/settings', icon: 'bi bi-gear' },
  { name: 'Log User Access', path: adminPath.value + '/logs', icon: 'bi bi-journal-text', pulse: true },
])

// Robust Active State Checker for any Menu Item (Built-in or Dynamic Custom)
const isMenuItemActive = (item) => {
  if (!item) return false
  const currentUrl = page.url.split('?')[0]
  
  let rawUrl = item.url || item.path || ''
  if (!rawUrl || rawUrl === '#') return false

  let targetPath = rawUrl.replace(/^https?:\/\/[^\/]+/, '').split('?')[0]

  if (!targetPath.startsWith('/')) {
    targetPath = adminPath.value + '/' + targetPath
  }

  targetPath = targetPath.replace(/\/+$/, '')
  const normCurrent = currentUrl.replace(/\/+$/, '')

  if (targetPath === adminPath.value) {
    return normCurrent === adminPath.value
  }

  return normCurrent === targetPath || normCurrent.startsWith(targetPath + '/')
}

// Parent Menu Submenu Accordion Open State
const expandedMenus = ref({})

const toggleSubmenu = (menuId) => {
  expandedMenus.value[menuId] = !expandedMenus.value[menuId]
}

const isParentActiveOrExpanded = (item) => {
  if (!item) return false
  if (expandedMenus.value[item.id] !== undefined) {
    return expandedMenus.value[item.id]
  }
  if (item.children && item.children.length) {
    return item.children.some(c => isMenuItemActive(c))
  }
  return false
}

// Theme (Dark / Light) State matching d1.html (#0c0b09 obsidian dark)
const isDark = ref(false)

const applyTheme = (dark) => {
  isDark.value = dark
  if (dark) {
    document.documentElement.classList.add('dark')
    document.body.style.backgroundColor = '#0c0b09'
    document.body.style.color = '#e7e5e4'
    localStorage.setItem('inermin_theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    document.body.style.backgroundColor = '#f7f5f1'
    document.body.style.color = '#1c1917'
    localStorage.setItem('inermin_theme', 'light')
  }
}

const toggleTheme = () => {
  applyTheme(!isDark.value)
}

// Accent Color State (amber, emerald, crimson, ocean, violet, bronze)
const accentColor = ref('amber')
const isColorPickerOpen = ref(false)
const colorSwatches = [
  { name: 'amber', label: 'Amber', style: 'background: linear-gradient(135deg, #f59e0b, #b45309);' },
  { name: 'emerald', label: 'Emerald', style: 'background: linear-gradient(135deg, #10b981, #047857);' },
  { name: 'crimson', label: 'Crimson', style: 'background: linear-gradient(135deg, #ef4444, #b91c1c);' },
  { name: 'ocean', label: 'Ocean', style: 'background: linear-gradient(135deg, #06b6d4, #0e7490);' },
  { name: 'violet', label: 'Violet', style: 'background: linear-gradient(135deg, #8b5cf6, #6d28d9);' },
  { name: 'bronze', label: 'Bronze', style: 'background: linear-gradient(135deg, #d97706, #92400e);' },
]

const setAccent = (color) => {
  accentColor.value = color
  document.documentElement.setAttribute('data-accent', color)
  localStorage.setItem('inermin_accent', color)
}

// Menu Live Search
const menuSearchQuery = ref('')
const searchInputRef = ref(null)

const filterGroup = (group) => {
  if (!menuSearchQuery.value.trim()) return group
  const q = menuSearchQuery.value.toLowerCase()
  return group.filter(m => m.name.toLowerCase().includes(q))
}

const filteredAdminGroup = computed(() => filterGroup(adminGroupMenus.value))
const filteredDevToolsGroup = computed(() => filterGroup(devToolsGroupMenus.value))
const filteredSystemGroup = computed(() => filterGroup(systemGroupMenus.value))
const filteredCustomMenu = computed(() => {
  if (!menuSearchQuery.value.trim()) return menu.value
  const q = menuSearchQuery.value.toLowerCase()
  return menu.value.filter(m => {
    const matchParent = m.name.toLowerCase().includes(q)
    const matchChild = m.children && m.children.some(c => c.name.toLowerCase().includes(q))
    return matchParent || matchChild
  })
})

// Collapsible Sidebar & Mobile State
const isCollapsed = ref(false)
const isMobileOpen = ref(false)

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const toggleMobileMenu = () => {
  isMobileOpen.value = !isMobileOpen.value
}

const isUserDropdownOpen = ref(false)

// Breadcrumbs Computation
const breadcrumbs = computed(() => {
  const url = page.url.split('?')[0]
  const cleanPath = url.replace(adminPath.value, '').replace(/^\//, '')
  if (!cleanPath) return [{ name: 'Dashboard', path: adminPath.value, isLast: true, isLink: false }]
  
  const parts = cleanPath.split('/')
  const crumbs = [{ name: 'Dashboard', path: adminPath.value, isLast: false, isLink: true }]
  let current = adminPath.value
  const nonClickableActions = ['edit', 'add', 'detail', 'step1', 'step2', 'step3', 'step4', 'delete']

  parts.forEach((p, idx) => {
    current += '/' + p
    const lower = p.toLowerCase()
    const formatted = p.replace(/_/g, ' ').replace(/-/g, ' ')
    const isActionKeyword = nonClickableActions.includes(lower)
    const isLastItem = idx === parts.length - 1

    crumbs.push({
      name: formatted.charAt(0).toUpperCase() + formatted.slice(1),
      path: current,
      isLast: isLastItem,
      isLink: !isLastItem && !isActionKeyword
    })
  })
  return crumbs
})

// Keyboard shortcuts (Ctrl+K / Cmd+K)
const handleKeyDown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    if (searchInputRef.value) {
      searchInputRef.value.focus()
    }
  }
}

onMounted(() => {
  const savedTheme = localStorage.getItem('inermin_theme') || page.props.default_theme || 'dark'
  applyTheme(savedTheme === 'dark')

  const savedAccent = localStorage.getItem('inermin_accent') || page.props.primary_color || 'amber'
  accentColor.value = savedAccent
  document.documentElement.setAttribute('data-accent', savedAccent)

  window.addEventListener('keydown', handleKeyDown)
})
</script>

<template>
  <div class="min-h-screen bg-canvas text-stone-900 dark:text-stone-100 font-sans flex antialiased selection:bg-[rgb(var(--accent-rgb))] selection:text-white">
    
    <!-- Mobile Backdrop -->
    <Transition name="fade">
      <div
        v-if="isMobileOpen"
        @click="isMobileOpen = false"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
      ></div>
    </Transition>

    <!-- Aether Console Sidebar -->
    <aside
      :class="[
        'fixed lg:sticky top-0 h-screen z-50 flex flex-col bg-white dark:bg-[#15130f] border-r border-stone-200 dark:border-white/5 shadow-2xl transition-all duration-300 ease-in-out shrink-0 select-none',
        isCollapsed ? 'lg:w-20' : 'lg:w-72',
        isMobileOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <!-- Sidebar Header (Aether Brand Logo) -->
      <div class="h-20 flex items-center justify-between px-5 border-b border-stone-200 dark:border-white/5 shrink-0">
        <Link :href="adminPath" class="flex items-center gap-3.5 overflow-hidden group">
          <div class="relative w-11 h-11 rounded-2xl flex items-center justify-center text-white font-bold text-xl font-display shadow-lg shadow-[rgba(var(--accent-rgb),0.3)] transition-transform group-hover:scale-105 shrink-0"
               style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));">
            <span>Æ</span>
            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-white dark:border-[#15130f]"></div>
          </div>
          <div v-if="!isCollapsed || isMobileOpen" class="flex flex-col truncate">
            <span class="font-display font-bold text-[17px] tracking-tight text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition-colors">
              {{ appName }}
            </span>
            <span class="text-[10px] tracking-[0.25em] text-stone-400 font-semibold uppercase flex items-center gap-1.5">
              <span>SUPERADMIN</span>
            </span>
          </div>
        </Link>

        <!-- Collapse Button -->
        <button
          @click="toggleSidebar"
          class="hidden lg:flex p-1.5 rounded-lg text-stone-400 hover:text-stone-700 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-white/5 transition"
          title="Toggle Sidebar Collapse"
        >
          <i :class="['bi text-sm transition-transform duration-300', isCollapsed ? 'bi-chevron-double-right' : 'bi-chevron-left']"></i>
        </button>
      </div>

      <!-- Sidebar Search Bar -->
      <div v-if="!isCollapsed || isMobileOpen" class="px-4 pt-4 pb-2 shrink-0">
        <div class="relative flex items-center">
          <input
            ref="searchInputRef"
            v-model="menuSearchQuery"
            type="text"
            placeholder="Search menu..."
            class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200/60 dark:border-white/5 rounded-xl pl-9 pr-14 py-2 text-xs text-stone-900 dark:text-stone-100 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] transition"
          />
          <i class="bi bi-search absolute left-3 text-stone-400 text-xs pointer-events-none"></i>
          <kbd class="absolute right-2.5 text-[10px] text-stone-400 bg-white dark:bg-white/5 border border-stone-200 dark:border-white/10 px-1.5 py-0.5 rounded font-mono pointer-events-none">⌘K</kbd>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="flex-1 overflow-y-auto px-3 py-3 space-y-5 custom-scrollbar">
        
        <!-- SECTION 1: TOP APPLICATION MODULES (WITHOUT TITLE HEADER) -->
        <div>
          <ul class="space-y-1">
            <li>
              <Link
                :href="adminPath"
                :class="[
                  'nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200',
                  isMenuItemActive({ path: adminPath })
                    ? 'active text-[rgb(var(--accent-rgb))] font-bold'
                    : 'text-stone-600 dark:text-stone-300'
                ]"
              >
                <i class="bi bi-grid-1x2 nav-icon text-base shrink-0"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate">Dashboard</span>
              </Link>
            </li>

            <!-- Custom Dynamic User Generated Menus (Supports Parent & Child Submenus) -->
            <template v-for="item in filteredCustomMenu" :key="item.id">
              
              <!-- IF PARENT WITH CHILDREN SUBMENU -->
              <li v-if="item.children && item.children.length" class="space-y-1">
                <div
                  @click="toggleSubmenu(item.id)"
                  :class="[
                    'nav-item group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 cursor-pointer select-none',
                    isParentActiveOrExpanded(item) ? 'text-[rgb(var(--accent-rgb))] font-bold bg-stone-100/50 dark:bg-white/[0.03]' : 'text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white'
                  ]"
                >
                  <div class="flex items-center gap-3 truncate">
                    <i :class="[item.icon || 'bi bi-folder2-open', 'nav-icon text-base shrink-0']"></i>
                    <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ item.name }}</span>
                  </div>
                  <i v-if="!isCollapsed || isMobileOpen" :class="['bi text-xs transition-transform duration-200 text-stone-400', isParentActiveOrExpanded(item) ? 'bi-chevron-down' : 'bi-chevron-right']"></i>
                </div>

                <!-- Child Submenu Items -->
                <ul v-if="isParentActiveOrExpanded(item) && (!isCollapsed || isMobileOpen)" class="relative ml-4 pl-3 my-1 border-l border-stone-200 dark:border-white/10 space-y-1">
                  <li v-for="child in item.children" :key="child.id">
                    <Link
                      :href="child.url || (adminPath + '/' + child.path)"
                      :class="[
                        'nav-item group flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs transition-all duration-200 font-medium',
                        isMenuItemActive(child)
                          ? 'active text-[rgb(var(--accent-rgb))] font-bold bg-[rgb(var(--accent-rgb))]/10'
                          : 'text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white hover:bg-stone-100/60 dark:hover:bg-white/5'
                      ]"
                    >
                      <i :class="[child.icon || 'bi bi-circle', 'text-[11px] shrink-0 text-stone-400 group-hover:text-[rgb(var(--accent-rgb))] transition-colors']"></i>
                      <span class="truncate">{{ child.name }}</span>
                    </Link>
                  </li>
                </ul>
              </li>

              <!-- SINGLE TOP-LEVEL MENU ITEM -->
              <li v-else>
                <Link
                  :href="item.url || (adminPath + '/' + item.path)"
                  :class="[
                    'nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200',
                    isMenuItemActive(item)
                      ? 'active text-[rgb(var(--accent-rgb))] font-bold'
                      : 'text-stone-600 dark:text-stone-300'
                  ]"
                >
                  <i :class="[item.icon || 'bi bi-folder2', 'nav-icon text-base shrink-0']"></i>
                  <span v-if="!isCollapsed || isMobileOpen" class="truncate">{{ item.name }}</span>
                </Link>
              </li>

            </template>
          </ul>
        </div>

        <!-- SECTION 2: ADMINISTRATION -->
        <div v-if="user.is_superadmin && filteredAdminGroup.length">
          <div v-if="!isCollapsed || isMobileOpen" class="px-3 mb-2 text-[10px] tracking-[0.22em] text-stone-400 font-semibold uppercase">
            ADMINISTRATION
          </div>

          <ul class="space-y-1">
            <li v-for="item in filteredAdminGroup" :key="item.path">
              <Link
                :href="item.path"
                :class="[
                  'nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200',
                  isMenuItemActive(item)
                    ? 'active text-[rgb(var(--accent-rgb))] font-bold'
                    : 'text-stone-600 dark:text-stone-300'
                ]"
              >
                <i :class="[item.icon, 'nav-icon text-base shrink-0']"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate flex-1">{{ item.name }}</span>
              </Link>
            </li>
          </ul>
        </div>

        <!-- SECTION 3: DEVELOPER TOOLS -->
        <div v-if="user.is_superadmin && filteredDevToolsGroup.length">
          <div v-if="!isCollapsed || isMobileOpen" class="px-3 mb-2 text-[10px] tracking-[0.22em] text-stone-400 font-semibold uppercase">
            DEVELOPER TOOLS
          </div>

          <ul class="space-y-1">
            <li v-for="item in filteredDevToolsGroup" :key="item.path">
              <Link
                :href="item.path"
                :class="[
                  'nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200',
                  isMenuItemActive(item)
                    ? 'active text-[rgb(var(--accent-rgb))] font-bold'
                    : 'text-stone-600 dark:text-stone-300'
                ]"
              >
                <i :class="[item.icon, 'nav-icon text-base shrink-0']"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate flex-1">{{ item.name }}</span>
                <span v-if="item.badge && (!isCollapsed || isMobileOpen)" class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                  {{ item.badge }}
                </span>
              </Link>
            </li>
          </ul>
        </div>

        <!-- SECTION 4: SYSTEM -->
        <div v-if="user.is_superadmin && filteredSystemGroup.length">
          <div v-if="!isCollapsed || isMobileOpen" class="px-3 mb-2 text-[10px] tracking-[0.22em] text-stone-400 font-semibold uppercase">
            SYSTEM
          </div>

          <ul class="space-y-1">
            <li v-for="item in filteredSystemGroup" :key="item.path">
              <Link
                :href="item.path"
                :class="[
                  'nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200',
                  isMenuItemActive(item)
                    ? 'active text-[rgb(var(--accent-rgb))] font-bold'
                    : 'text-stone-600 dark:text-stone-300'
                ]"
              >
                <i :class="[item.icon, 'nav-icon text-base shrink-0']"></i>
                <span v-if="!isCollapsed || isMobileOpen" class="truncate flex-1">{{ item.name }}</span>
                <span v-if="item.pulse && (!isCollapsed || isMobileOpen)" class="w-2 h-2 rounded-full bg-rose-500 pulse-dot"></span>
              </Link>
            </li>
          </ul>
        </div>

      </div>

      <!-- User Profile Card -->
      <div class="p-3 border-t border-stone-200 dark:border-white/5 shrink-0">
        <Link
          :href="adminPath + '/profile'"
          class="flex items-center gap-3 p-2.5 rounded-xl bg-stone-100/60 dark:bg-white/5 hover:bg-stone-200/60 dark:hover:bg-white/10 border border-stone-200/50 dark:border-white/5 transition group"
          title="Edit Profile & Settings"
        >
          <div class="relative shrink-0">
            <img :src="user.photo || '/vendor/inermin/avatar.svg'" @error="(e) => e.target.src = '/vendor/inermin/avatar.svg'" class="w-9 h-9 rounded-xl object-cover ring-2 ring-[rgba(var(--accent-rgb),0.3)]" alt="Avatar" />
            <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white dark:border-[#15130f]"></div>
          </div>
          
          <div v-if="!isCollapsed || isMobileOpen" class="overflow-hidden leading-tight flex-1">
            <h4 class="font-bold text-xs text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition-colors truncate">{{ user.name }}</h4>
            <span class="text-[10px] text-stone-400 font-medium truncate block mt-0.5">{{ user.privilege_name }}</span>
          </div>
          <i v-if="!isCollapsed || isMobileOpen" class="bi bi-gear-fill text-stone-400 group-hover:text-[rgb(var(--accent-rgb))] text-xs transition"></i>
        </Link>
      </div>
    </aside>

    <!-- Main Content Shell -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
      
      <!-- Topbar Header -->
      <header class="sticky top-0 z-30 glass bg-white/75 dark:bg-[#0c0b09]/75 border-b border-stone-200 dark:border-white/5 px-4 lg:px-8 h-16 flex items-center justify-between transition-colors">
        
        <!-- Left: Mobile Menu Toggle & Breadcrumbs -->
        <div class="flex items-center gap-4">
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-xl text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-white/5 lg:hidden transition"
          >
            <i class="bi bi-list text-xl"></i>
          </button>

          <!-- Breadcrumbs Nav -->
          <nav class="hidden md:flex items-center gap-2 text-xs font-semibold">
            <template v-for="(crumb, idx) in breadcrumbs" :key="idx">
              <i v-if="idx > 0" class="bi bi-chevron-right text-[10px] text-stone-400"></i>
              
              <Link
                v-if="crumb.isLink"
                :href="crumb.path"
                class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 transition"
              >
                {{ crumb.name }}
              </Link>
              
              <span
                v-else-if="!crumb.isLast"
                class="text-stone-400 cursor-default"
              >
                {{ crumb.name }}
              </span>

              <span
                v-else
                class="font-bold text-[rgb(var(--accent-rgb))]"
              >
                {{ crumb.name }}
              </span>
            </template>
          </nav>
        </div>

        <!-- Right Utilities & Accent Picker -->
        <div class="flex items-center gap-3">
          
          <!-- Quick Find Search Input -->
          <div class="hidden md:flex items-center gap-2 bg-stone-100 dark:bg-white/5 px-3 py-1.5 rounded-xl border border-stone-200/60 dark:border-white/5 text-stone-400 text-xs font-medium">
            <i class="bi bi-search text-stone-400"></i>
            <span>Quick find...</span>
          </div>

          <!-- Color Accent Swatch Selector Dropdown -->
          <div class="relative">
            <button
              @click="isColorPickerOpen = !isColorPickerOpen"
              class="p-2 rounded-xl hover:bg-stone-100 dark:hover:bg-white/5 flex items-center gap-2 transition"
              title="Theme Color Accent"
            >
              <span class="w-5 h-5 rounded-full shadow-sm" style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"></span>
              <i class="bi bi-chevron-down text-xs text-stone-400 hidden sm:block"></i>
            </button>

            <!-- Color Swatches Panel -->
            <Transition name="dropdown">
              <div
                v-if="isColorPickerOpen"
                @click="isColorPickerOpen = false"
                class="absolute right-0 mt-2 w-48 card rounded-2xl p-3.5 shadow-2xl z-50 space-y-2 border border-stone-200 dark:border-white/10"
              >
                <div class="text-[10px] tracking-[0.2em] text-stone-400 font-semibold uppercase px-1">
                  ACCENT COLOR
                </div>
                <div class="grid grid-cols-3 gap-2.5">
                  <button
                    v-for="swatch in colorSwatches"
                    :key="swatch.name"
                    @click="setAccent(swatch.name)"
                    :class="[
                      'w-9 h-9 rounded-full transition-transform duration-200 flex items-center justify-center border-2',
                      accentColor === swatch.name ? 'scale-110 border-stone-900 dark:border-white shadow-md' : 'border-transparent hover:scale-105'
                    ]"
                    :style="swatch.style"
                    :title="swatch.label"
                  >
                    <i v-if="accentColor === swatch.name" class="bi bi-check-lg text-white text-sm"></i>
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Theme Toggle -->
          <button
            @click="toggleTheme"
            class="p-2.5 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-white/5 transition"
            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <i v-if="isDark" class="bi bi-sun-fill text-base text-amber-400"></i>
            <i v-else class="bi bi-moon-stars-fill text-base text-stone-700"></i>
          </button>

          <!-- App Switcher Launcher -->
          <AppSwitcher />

          <!-- Interactive Notification Bell Dropdown -->
          <div class="relative">
            <button
              @click="isNotificationsOpen = !isNotificationsOpen"
              class="relative p-2.5 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-white/5 transition focus:outline-none"
              title="Notifications"
            >
              <i class="bi bi-bell text-base"></i>
              <span
                v-if="unreadCount > 0"
                class="absolute top-1.5 right-1.5 min-w-4 h-4 px-1 rounded-full text-[9px] font-extrabold text-white flex items-center justify-center border-2 border-white dark:border-[#0c0b09]"
                style="background: rgb(var(--accent-rgb));"
              >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <!-- Notifications Dropdown Panel -->
            <Transition name="dropdown">
              <div
                v-if="isNotificationsOpen"
                class="absolute right-0 mt-2 w-80 sm:w-96 card rounded-2xl p-0 shadow-2xl z-50 overflow-hidden border border-stone-200 dark:border-white/10"
              >
                <!-- Panel Header -->
                <div class="p-4 border-b border-stone-100 dark:border-white/5 flex items-center justify-between bg-stone-50/50 dark:bg-white/[0.02]">
                  <div class="flex items-center gap-2">
                    <span class="font-display font-bold text-sm text-stone-900 dark:text-white">Notifications</span>
                    <span
                      v-if="unreadCount > 0"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white"
                      style="background: rgb(var(--accent-rgb));"
                    >
                      {{ unreadCount }} new
                    </span>
                  </div>

                  <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-[11px] font-bold text-[rgb(var(--accent-rgb))] hover:underline flex items-center gap-1"
                  >
                    <i class="bi bi-check2-all"></i>
                    <span>Mark all read</span>
                  </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-stone-100 dark:divide-white/5 custom-scrollbar">
                  <template v-if="notificationItems.length">
                    <div
                      v-for="item in notificationItems"
                      :key="item.id"
                      @click="toggleNotification(item)"
                      :class="[
                        'p-3.5 flex items-start gap-3 transition cursor-pointer hover:bg-stone-50 dark:hover:bg-white/[0.03]',
                        !item.is_read ? 'bg-amber-500/[0.04] dark:bg-[rgba(var(--accent-rgb),0.06)]' : ''
                      ]"
                    >
                      <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 text-sm" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                        <i :class="item.icon || 'bi bi-bell-fill'"></i>
                      </div>

                      <div class="flex-1 overflow-hidden leading-tight space-y-1">
                        <p class="text-xs font-semibold text-stone-800 dark:text-stone-200 line-clamp-2">
                          {{ item.content }}
                        </p>
                        <p class="text-[10px] text-stone-400 font-medium">
                          {{ item.created_at }}
                        </p>
                      </div>

                      <span v-if="!item.is_read" class="w-2 h-2 rounded-full shrink-0 mt-1.5" style="background: rgb(var(--accent-rgb));"></span>
                    </div>
                  </template>

                  <!-- Empty State -->
                  <div v-else class="p-8 text-center space-y-2">
                    <i class="bi bi-bell-slash text-2xl text-stone-400"></i>
                    <p class="text-xs font-bold text-stone-700 dark:text-stone-300">All caught up!</p>
                    <p class="text-[11px] text-stone-400">No new system notifications right now.</p>
                  </div>
                </div>

                <!-- Panel Footer -->
                <div class="p-2.5 text-center border-t border-stone-100 dark:border-white/5 bg-stone-50/50 dark:bg-white/[0.02]">
                  <Link
                    :href="adminPath + '/logs'"
                    @click="isNotificationsOpen = false"
                    class="text-xs font-bold text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white transition inline-flex items-center gap-1.5"
                  >
                    <span>View all system activity logs</span>
                    <i class="bi bi-arrow-right"></i>
                  </Link>
                </div>

              </div>
            </Transition>
          </div>

          <div class="w-px h-6 bg-stone-200 dark:bg-white/10 hidden sm:block"></div>

          <!-- Profile Dropdown -->
          <div class="relative">
            <button
              @click="isUserDropdownOpen = !isUserDropdownOpen"
              class="flex items-center gap-3 p-1 rounded-xl hover:bg-stone-100 dark:hover:bg-white/5 transition"
            >
              <img :src="user.photo || '/vendor/inermin/avatar.svg'" @error="(e) => e.target.src = '/vendor/inermin/avatar.svg'" class="w-8 h-8 rounded-xl object-cover ring-2 ring-[rgba(var(--accent-rgb),0.3)]" alt="Avatar" />
            </button>

            <Transition name="dropdown">
              <div
                v-if="isUserDropdownOpen"
                @click="isUserDropdownOpen = false"
                class="absolute right-0 mt-2 w-56 card rounded-2xl shadow-2xl p-2 z-50 space-y-1 border border-stone-200 dark:border-white/10"
              >
                <div class="px-4 py-2.5 border-b border-stone-100 dark:border-white/5">
                  <p class="text-xs font-bold text-stone-900 dark:text-white">{{ user.name }}</p>
                  <p class="text-[10px] font-bold tracking-wider uppercase mt-0.5" style="color: rgb(var(--accent-rgb));">{{ user.privilege_name }}</p>
                </div>

                <div class="py-1 border-b border-stone-100 dark:border-white/5 space-y-0.5">
                  <Link
                    :href="adminPath + '/profile'"
                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 rounded-xl transition"
                  >
                    <i class="bi bi-person-bounding-box text-sm"></i>
                    <span>Edit Profile</span>
                  </Link>

                  <Link
                    :href="adminPath + '/profile?tab=password'"
                    class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 rounded-xl transition"
                  >
                    <i class="bi bi-key-fill text-sm"></i>
                    <span>Change Password</span>
                  </Link>
                </div>

                <Link
                  :href="adminPath + '/logout'"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition"
                >
                  <i class="bi bi-box-arrow-right text-base"></i>
                  <span>Sign Out</span>
                </Link>
              </div>
            </Transition>
          </div>

        </div>
      </header>

      <!-- Main Page Content Area -->
      <main class="flex-1 p-4 lg:p-8 max-w-[1600px] mx-auto w-full space-y-6">
        
        <!-- Flash Alert Messages -->
        <Transition name="fade">
          <div v-if="flash.success" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center gap-3 shadow-xs">
            <i class="bi bi-check-circle-fill text-base"></i>
            <span>{{ flash.success }}</span>
          </div>
        </Transition>
        
        <Transition name="fade">
          <div v-if="flash.error || flash.message" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center gap-3 shadow-xs">
            <i class="bi bi-exclamation-triangle-fill text-base"></i>
            <span>{{ flash.error || flash.message }}</span>
          </div>
        </Transition>

        <slot />
      </main>

      <!-- Footer -->
      <footer class="py-6 px-8 border-t border-stone-200/80 dark:border-white/5 text-center text-xs text-stone-400 font-medium flex flex-col sm:flex-row items-center justify-between gap-3 max-w-[1600px] mx-auto w-full">
        <div>&copy; {{ new Date().getFullYear() }} Aether Console &bull; Inermin Executive SPA Admin</div>
        <div class="flex items-center gap-4">
          <a href="#" class="hover:text-stone-600 dark:hover:text-stone-200 transition">Documentation</a>
          <a href="#" class="hover:text-stone-600 dark:hover:text-stone-200 transition">Changelog</a>
          <a href="#" class="hover:text-stone-600 dark:hover:text-stone-200 transition">Support</a>
        </div>
      </footer>

    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap');

/* Accent tokens */
:root, [data-accent="amber"] {
  --accent-rgb: 217, 119, 6;
  --accent-soft: 245, 158, 11;
  --accent-deep: 180, 83, 9;
}

[data-accent="emerald"] {
  --accent-rgb: 5, 150, 105;
  --accent-soft: 16, 185, 129;
  --accent-deep: 4, 120, 87;
}

[data-accent="crimson"] {
  --accent-rgb: 220, 38, 38;
  --accent-soft: 239, 68, 68;
  --accent-deep: 185, 28, 28;
}

[data-accent="ocean"] {
  --accent-rgb: 14, 116, 144;
  --accent-soft: 6, 182, 212;
  --accent-deep: 8, 145, 178;
}

[data-accent="violet"] {
  --accent-rgb: 124, 58, 237;
  --accent-soft: 139, 92, 246;
  --accent-deep: 109, 40, 217;
}

[data-accent="bronze"] {
  --accent-rgb: 180, 83, 9;
  --accent-soft: 217, 119, 6;
  --accent-deep: 146, 64, 14;
}

html, body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  transition: background-color 0.3s ease, color 0.3s ease;
}

body {
  background-color: #f7f5f1;
  color: #1c1917;
}

.dark body {
  background-color: #0c0b09 !important;
  color: #e7e5e4 !important;
}

.font-display {
  font-family: 'Space Grotesk', sans-serif;
}

/* Background Canvas Gradients matching d1.html */
.bg-canvas {
  background-image:
    radial-gradient(circle at 12% 8%, rgba(var(--accent-rgb), 0.10), transparent 35%),
    radial-gradient(circle at 88% 92%, rgba(var(--accent-soft), 0.08), transparent 40%);
  background-attachment: fixed;
}

.dark .bg-canvas {
  background-image:
    radial-gradient(circle at 12% 8%, rgba(var(--accent-rgb), 0.18), transparent 35%),
    radial-gradient(circle at 88% 92%, rgba(var(--accent-soft), 0.10), transparent 40%);
  background-attachment: fixed;
}

/* Glassmorphism Header */
.glass {
  backdrop-filter: blur(14px) saturate(140%);
  -webkit-backdrop-filter: blur(14px) saturate(140%);
}

/* Cards matching d1.html (#15130f obsidian card) */
.card {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.dark .card {
  background: #15130f !important;
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
}

/* Sidebar Nav Indicators */
.nav-item {
  position: relative;
  overflow: hidden;
  transition: color .2s ease, background-color .2s ease;
}

.nav-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0;
  background: rgb(var(--accent-rgb));
  border-radius: 0 3px 3px 0;
  transition: height .25s ease;
}

.nav-item:hover {
  background: rgba(var(--accent-rgb), 0.06);
  color: rgb(var(--accent-rgb));
}

.nav-item.active {
  background: linear-gradient(90deg, rgba(var(--accent-rgb), 0.16) 0%, rgba(var(--accent-rgb), 0.03) 100%);
  color: rgb(var(--accent-rgb));
}

.nav-item.active::before {
  height: 60%;
}

.nav-item .nav-icon {
  transition: color .2s, transform .2s;
}

.nav-item:hover .nav-icon {
  transform: translateX(2px);
}

/* Pulse Dot Animation */
@keyframes pulse-soft {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(var(--accent-rgb), 0.6);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(var(--accent-rgb), 0);
  }
}

.pulse-dot {
  animation: pulse-soft 2s infinite;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(120, 113, 108, 0.25);
  border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(120, 113, 108, 0.45);
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease-out;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
