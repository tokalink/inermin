<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  menus: Array,
  all_menus: Array,
  modules: Array,
  privileges: Array,
  parent_menus: Array,
})

const activeMenuForm = ref({
  id: 0,
  name: '',
  type: 'Module',
  icon: 'bi bi-grid',
  path: '',
  module_id: props.modules?.[0]?.id || 0,
  parent_id: 0,
  is_active: 1,
  privileges: props.privileges?.map(p => p.id) || [],
})

const isEditing = ref(false)

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
    is_active: 1,
    privileges: props.privileges?.map(p => p.id) || [],
  }
}

const saveMenu = () => {
  router.post('/administrator/menus/save', activeMenuForm.value, {
    onSuccess: () => resetForm(),
  })
}

const deleteMenu = (id, name) => {
  if (confirm(`Are you sure you want to delete menu "${name}"?`)) {
    window.location.href = `/administrator/menus/delete/${id}`
  }
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage, sort, and structure admin navigation menus</p>
        </div>
      </div>

      <!-- Menu Builder Grid Layout (Left: Tree Hierarchy, Right: Create/Edit Form) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Menu Tree Hierarchy -->
        <div class="lg:col-span-2 space-y-4">
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center justify-between">
              <span>Navigation Menu Structure</span>
              <span class="text-xs text-slate-400 font-normal">Parent & Child Submenus</span>
            </h3>

            <div class="space-y-2">
              <div v-for="item in menus" :key="item.id" class="space-y-2">
                
                <!-- Parent Item Card -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <i :class="[item.icon || 'bi bi-grid', 'text-indigo-600 dark:text-indigo-400 text-lg']"></i>
                    <div>
                      <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ item.name }}</h4>
                      <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ item.type }} • {{ item.path || '-' }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-1">
                    <button @click="editMenu(item)" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition">
                      <i class="bi bi-pencil text-sm"></i>
                    </button>
                    <button @click="deleteMenu(item.id, item.name)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition">
                      <i class="bi bi-trash text-sm"></i>
                    </button>
                  </div>
                </div>

                <!-- Submenus (Children) -->
                <div v-if="item.children && item.children.length" class="pl-6 space-y-2">
                  <div v-for="child in item.children" :key="child.id" class="p-3 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <i :class="[child.icon || 'bi bi-grid', 'text-slate-500 text-sm']"></i>
                      <div>
                        <h5 class="font-semibold text-xs text-slate-800 dark:text-slate-200">{{ child.name }}</h5>
                        <p class="text-[10px] text-slate-400 font-mono">{{ child.type }} • {{ child.path }}</p>
                      </div>
                    </div>

                    <div class="flex items-center gap-1">
                      <button @click="editMenu(child)" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950 transition">
                        <i class="bi bi-pencil text-sm"></i>
                      </button>
                      <button @click="deleteMenu(child.id, child.name)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition">
                        <i class="bi bi-trash text-sm"></i>
                      </button>
                    </div>
                  </div>
                </div>

              </div>

              <div v-if="!menus || !menus.length" class="p-8 text-center text-slate-400">
                No menus created yet.
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
                <input v-model="activeMenuForm.name" type="text" required placeholder="e.g. Absen Data" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Type *</label>
                <select v-model="activeMenuForm.type" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100">
                  <option value="Module">Module</option>
                  <option value="Route">Route / Controller</option>
                  <option value="URL">External URL</option>
                </select>
              </div>

              <div v-if="activeMenuForm.type === 'Module'">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Module *</label>
                <select v-model="activeMenuForm.module_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100">
                  <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }} ({{ m.table_name }})</option>
                </select>
              </div>

              <div v-else>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Path / URL *</label>
                <input v-model="activeMenuForm.path" type="text" placeholder="e.g. AdminAbsenControllerGetIndex or /custom-url" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Parent Menu</label>
                <select v-model="activeMenuForm.parent_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100">
                  <option :value="0">-- Top Level (No Parent) --</option>
                  <option v-for="p in parent_menus" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Icon Class</label>
                <input v-model="activeMenuForm.icon" type="text" placeholder="bi bi-grid, fa fa-glass" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100" />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Privilege Permissions</label>
                <div class="space-y-1.5 max-h-40 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                  <div v-for="priv in privileges" :key="priv.id" class="flex items-center gap-2">
                    <input type="checkbox" :id="'priv_'+priv.id" :value="priv.id" v-model="activeMenuForm.privileges" class="rounded text-indigo-600" />
                    <label :for="'priv_'+priv.id" class="text-xs text-slate-800 dark:text-slate-200 font-semibold">{{ priv.name }}</label>
                  </div>
                </div>
              </div>

              <div class="pt-2 flex items-center justify-end gap-2">
                <button v-if="isEditing" type="button" @click="resetForm" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl">
                  Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition">
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
