<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  menus: Array,
  all_menus: Array,
  modules: Array,
  privileges: Array,
  parent_menus: Array,
  apps: Array,
})

const selectedAppFilter = ref('all')

const activeMenuForm = ref({
  id: 0,
  name: '',
  type: 'Module',
  icon: 'bi bi-grid',
  path: '',
  module_id: props.modules?.[0]?.id || 0,
  parent_id: 0,
  app_code: '',
  is_active: 1,
  privileges: props.privileges?.map(p => p.id) || [],
})

const isEditing = ref(false)

const filteredMenus = computed(() => {
  if (!props.menus) return []
  if (selectedAppFilter.value === 'all') return props.menus

  return props.menus.filter(item => {
    if (item.app_code === selectedAppFilter.value) return true
    if (item.children && item.children.some(c => c.app_code === selectedAppFilter.value)) return true
    return false
  })
})

const editMenu = (item) => {
  isEditing.value = true
  activeMenuForm.value = {
    id: item.id,
    name: item.name,
    type: item.type || 'Module',
    icon: item.icon || 'bi bi-grid',
    path: item.path || '',
    module_id: props.modules?.find(m => m.controller + 'GetIndex' === item.path || m.path === item.path)?.id || 0,
    parent_id: item.parent_id || 0,
    app_code: item.app_code || '',
    is_active: item.is_active ? 1 : 0,
    privileges: item.privileges || [],
  }
}

const resetForm = () => {
  isEditing.value = false
  activeMenuForm.value = {
    id: 0,
    name: '',
    type: 'Module',
    icon: 'bi bi-grid',
    path: '',
    module_id: props.modules?.[0]?.id || 0,
    parent_id: 0,
    app_code: '',
    is_active: 1,
    privileges: props.privileges?.map(p => p.id) || [],
  }
}

const saveMenu = () => {
  router.post('/administrator/menus/save', activeMenuForm.value, {
    onSuccess: () => resetForm(),
  })
}

const moveOrder = (id, direction) => {
  router.get(`/administrator/menus/move-order/${id}/${direction}`, {}, { preserveScroll: true })
}

const deleteMenu = (id, name) => {
  if (confirm(`Are you sure you want to delete menu "${name}"?`)) {
    window.location.href = `/administrator/menus/delete/${id}`
  }
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full max-w-7xl mx-auto">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage, group by application suite, and drag/order menu items</p>
        </div>
      </div>

      <!-- Application Suite Filter Tabs -->
      <div class="flex items-center gap-2 border-b border-stone-200 dark:border-stone-800 overflow-x-auto pb-1 custom-scrollbar">
        <button
          @click="selectedAppFilter = 'all'"
          :class="[
            'px-4 py-2 font-bold text-xs rounded-t-xl transition whitespace-nowrap flex items-center gap-2 border-b-2',
            selectedAppFilter === 'all'
              ? 'border-amber-500 text-amber-500 bg-amber-500/10'
              : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'
          ]"
        >
          <i class="bi bi-grid-3x3-gap-fill text-sm"></i>
          <span>All Applications</span>
          <span class="px-1.5 py-0.5 text-[10px] rounded-full bg-stone-200 dark:bg-stone-800 font-extrabold text-stone-700 dark:text-stone-300">
            {{ menus ? menus.length : 0 }}
          </span>
        </button>

        <button
          v-for="app in apps"
          :key="app.code"
          @click="selectedAppFilter = app.code"
          :class="[
            'px-4 py-2 font-bold text-xs rounded-t-xl transition whitespace-nowrap flex items-center gap-2 border-b-2',
            selectedAppFilter === app.code
              ? 'border-amber-500 text-amber-500 bg-amber-500/10'
              : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'
          ]"
        >
          <i :class="app.icon || 'bi bi-app-indicator'"></i>
          <span>{{ app.name }}</span>
        </button>
      </div>

      <!-- Menu Builder Grid Layout (Left: Tree Hierarchy & Order, Right: Create/Edit Form) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Menu Tree Hierarchy & Ordering -->
        <div class="lg:col-span-2 space-y-4">
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center justify-between">
              <span>Navigation Menu Structure</span>
              <span class="text-xs text-slate-400 font-normal">Order & Re-arrange Navigation</span>
            </h3>

            <div class="space-y-3">
              <div v-for="(item, idx) in filteredMenus" :key="item.id" class="space-y-2">
                
                <!-- Parent Item Card -->
                <div class="p-3.5 bg-stone-50 dark:bg-white/[0.02] border border-stone-200 dark:border-white/10 rounded-2xl flex items-center justify-between hover:border-amber-500/30 transition">
                  <div class="flex items-center gap-3">
                    <!-- Order Index Badge -->
                    <span class="w-6 h-6 rounded-lg bg-stone-200 dark:bg-white/10 text-stone-700 dark:text-stone-300 font-mono text-[11px] font-bold flex items-center justify-center">
                      #{{ idx + 1 }}
                    </span>

                    <i :class="[item.icon || 'bi bi-grid', 'text-amber-500 text-lg']"></i>
                    <div>
                      <div class="flex items-center gap-2">
                        <h4 class="font-bold text-xs text-stone-900 dark:text-white">{{ item.name }}</h4>
                        <span v-if="item.app_code" class="text-[10px] font-mono font-bold bg-amber-500/10 text-amber-500 px-1.5 py-0.2 rounded border border-amber-500/20 uppercase">
                          {{ item.app_code }}
                        </span>
                      </div>
                      <p class="text-[10px] text-stone-400 font-mono mt-0.5">{{ item.type }} • {{ item.path || '-' }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-1">
                    <!-- Up / Down Re-order Buttons -->
                    <button
                      @click="moveOrder(item.id, 'up')"
                      :disabled="idx === 0"
                      class="p-1.5 rounded-lg text-stone-400 hover:text-amber-500 hover:bg-amber-500/10 disabled:opacity-30 transition"
                      title="Move Up"
                    >
                      <i class="bi bi-arrow-up text-sm"></i>
                    </button>
                    <button
                      @click="moveOrder(item.id, 'down')"
                      :disabled="idx === filteredMenus.length - 1"
                      class="p-1.5 rounded-lg text-stone-400 hover:text-amber-500 hover:bg-amber-500/10 disabled:opacity-30 transition"
                      title="Move Down"
                    >
                      <i class="bi bi-arrow-down text-sm"></i>
                    </button>

                    <div class="w-px h-4 bg-stone-200 dark:bg-white/10 mx-1"></div>

                    <button @click="editMenu(item)" class="p-1.5 rounded-lg text-stone-400 hover:text-amber-500 hover:bg-amber-500/10 transition" title="Edit">
                      <i class="bi bi-pencil text-sm"></i>
                    </button>
                    <button @click="deleteMenu(item.id, item.name)" class="p-1.5 rounded-lg text-stone-400 hover:text-rose-500 hover:bg-rose-500/10 transition" title="Delete">
                      <i class="bi bi-trash text-sm"></i>
                    </button>
                  </div>
                </div>

                <!-- Submenus (Children) -->
                <div v-if="item.children && item.children.length" class="pl-8 space-y-2">
                  <div v-for="(child, cIdx) in item.children" :key="child.id" class="p-3 bg-stone-100/50 dark:bg-white/[0.01] border border-stone-200/70 dark:border-white/5 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <span class="w-5 h-5 rounded-md bg-stone-200/70 dark:bg-white/5 text-stone-500 font-mono text-[10px] font-bold flex items-center justify-center">
                        {{ cIdx + 1 }}
                      </span>
                      <i :class="[child.icon || 'bi bi-grid', 'text-stone-400 text-sm']"></i>
                      <div>
                        <h5 class="font-semibold text-xs text-stone-800 dark:text-stone-200">{{ child.name }}</h5>
                        <p class="text-[10px] text-stone-400 font-mono">{{ child.type }} • {{ child.path }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-1">
                      <button @click="moveOrder(child.id, 'up')" :disabled="cIdx === 0" class="p-1 rounded-md text-stone-400 hover:text-amber-500 disabled:opacity-30">
                        <i class="bi bi-arrow-up text-xs"></i>
                      </button>
                      <button @click="moveOrder(child.id, 'down')" :disabled="cIdx === item.children.length - 1" class="p-1 rounded-md text-stone-400 hover:text-amber-500 disabled:opacity-30">
                        <i class="bi bi-arrow-down text-xs"></i>
                      </button>
                      <div class="w-px h-3 bg-stone-200 dark:bg-white/10 mx-1"></div>
                      <button @click="editMenu(child)" class="p-1 rounded-md text-stone-400 hover:text-amber-500">
                        <i class="bi bi-pencil text-xs"></i>
                      </button>
                      <button @click="deleteMenu(child.id, child.name)" class="p-1 rounded-md text-stone-400 hover:text-rose-500">
                        <i class="bi bi-trash text-xs"></i>
                      </button>
                    </div>
                  </div>
                </div>

              </div>

              <div v-if="!filteredMenus || !filteredMenus.length" class="p-8 text-center text-stone-400 text-xs">
                <i class="bi bi-inbox text-3xl block mb-2 opacity-40"></i>
                No menus found in this application suite filter.
              </div>
            </div>

          </div>
        </div>

        <!-- Right Column: Create/Edit Form -->
        <div class="space-y-4">
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">
              {{ isEditing ? 'Edit Menu Item' : 'Add New Menu Item' }}
            </h3>

            <form @submit.prevent="saveMenu" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Menu Name *</label>
                <input v-model="activeMenuForm.name" type="text" required placeholder="e.g. Absen Data" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Application Suite Group</label>
                <select v-model="activeMenuForm.app_code" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white">
                  <option value="">-- General System Core --</option>
                  <option v-for="a in apps" :key="a.code" :value="a.code">{{ a.name }} ({{ a.code }})</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Type *</label>
                <select v-model="activeMenuForm.type" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white">
                  <option value="Module">Module (Database Module)</option>
                  <option value="Header">Header (Section Divider Label)</option>
                  <option value="Route">Route / Controller</option>
                  <option value="URL">External URL</option>
                </select>
              </div>

              <div v-if="activeMenuForm.type === 'Module'">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Module *</label>
                <select v-model="activeMenuForm.module_id" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white">
                  <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }} ({{ m.table_name }})</option>
                </select>
              </div>

              <div v-else>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Path / URL *</label>
                <input v-model="activeMenuForm.path" type="text" placeholder="e.g. AdminAbsenControllerGetIndex or /custom-url" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Parent Menu</label>
                <select v-model="activeMenuForm.parent_id" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white">
                  <option :value="0">-- Top Level (No Parent) --</option>
                  <option v-for="p in parent_menus" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Icon Class</label>
                <input v-model="activeMenuForm.icon" type="text" placeholder="bi bi-grid, bi bi-people" class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-2xl px-3.5 py-2.5 text-xs text-stone-900 dark:text-white" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Privilege Permissions</label>
                <div class="space-y-1.5 max-h-40 overflow-y-auto p-3 bg-stone-100 dark:bg-white/5 rounded-2xl border border-stone-200 dark:border-white/10">
                  <div v-for="priv in privileges" :key="priv.id" class="flex items-center gap-2">
                    <input type="checkbox" :id="'priv_'+priv.id" :value="priv.id" v-model="activeMenuForm.privileges" class="rounded text-amber-500" />
                    <label :for="'priv_'+priv.id" class="text-xs text-stone-800 dark:text-stone-200 font-semibold">{{ priv.name }}</label>
                  </div>
                </div>
              </div>

              <div class="pt-2 flex items-center justify-end gap-2">
                <button v-if="isEditing" type="button" @click="resetForm" class="px-4 py-2.5 bg-stone-100 dark:bg-white/5 text-stone-600 dark:text-stone-300 font-bold text-xs rounded-2xl">
                  Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold text-xs rounded-2xl shadow-md transition">
                  {{ isEditing ? 'Update Menu' : 'Save Menu' }}
                </button>
              </div>

            </form>
          </div>
        </div>

      </div>

    </div>
  </InerminAppLayout>
</template>
