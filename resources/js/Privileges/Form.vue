<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  row: Object,
  modules: Array,
  roles: Object,
  action_url: String,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

const initialRoles = {}
if (props.modules) {
  props.modules.forEach(m => {
    const existing = (props.roles && props.roles[m.id]) || {}
    initialRoles[m.id] = {
      is_visible: existing.is_visible ?? 1,
      is_create: existing.is_create ?? 1,
      is_read: existing.is_read ?? 1,
      is_edit: existing.is_edit ?? 1,
      is_delete: existing.is_delete ?? 1,
    }
  })
}

const form = useForm({
  name: props.row ? props.row.name : '',
  is_superadmin: props.row ? String(props.row.is_superadmin) : '0',
  theme_color: props.row ? (props.row.theme_color || 'skin-blue') : 'skin-blue',
  roles: initialRoles,
})

const toggleRow = (moduleId) => {
  const r = form.roles[moduleId]
  const allChecked = r.is_visible && r.is_create && r.is_read && r.is_edit && r.is_delete
  const newVal = allChecked ? 0 : 1
  r.is_visible = newVal
  r.is_create = newVal
  r.is_read = newVal
  r.is_edit = newVal
  r.is_delete = newVal
}

const toggleColumn = (colName) => {
  if (!props.modules) return
  const allChecked = props.modules.every(m => form.roles[m.id] && form.roles[m.id][colName])
  const newVal = allChecked ? 0 : 1
  props.modules.forEach(m => {
    if (form.roles[m.id]) {
      form.roles[m.id][colName] = newVal
    }
  })
}

const submit = () => {
  form.post(props.action_url)
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-6xl mx-auto space-y-6 font-sans">
      
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage privilege name, superadmin status, and module permission roles</p>
        </div>

        <Link
          :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')"
          class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 font-semibold text-xs rounded-xl shadow-sm transition"
        >
          <i class="bi bi-arrow-left text-sm"></i>
          <span>Back to List</span>
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        
        <!-- General Privilege Details Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
          <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 pb-2 border-b border-slate-100 dark:border-slate-800">
            Privilege Info
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Name -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Privilege Name <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="e.g. Manager, HR Staff"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
              />
            </div>

            <!-- Is Superadmin -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Is Superadmin
              </label>
              <select
                v-model="form.is_superadmin"
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
              >
                <option value="0">No (Standard Permissions)</option>
                <option value="1">Yes (Full Access Override)</option>
              </select>
            </div>

            <!-- Theme Color -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Theme Accent Color
              </label>
              <select
                v-model="form.theme_color"
                class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl px-3.5 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
              >
                <option value="amber">Amber Gold (Default)</option>
                <option value="emerald">Emerald Green</option>
                <option value="crimson">Crimson Red</option>
                <option value="ocean">Ocean Blue</option>
                <option value="violet">Royal Violet</option>
                <option value="bronze">Warm Bronze</option>
              </select>
            </div>

          </div>
        </div>

        <!-- Module Privileges Matrix Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                Module Privileges Roles Matrix
              </h2>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">Set fine-grained CRUD permissions per module</p>
            </div>

            <div class="flex items-center gap-2 text-xs">
              <span class="text-slate-400">Quick Toggle Columns:</span>
              <button type="button" @click="toggleColumn('is_visible')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] text-slate-600 dark:text-slate-300 transition">Visible</button>
              <button type="button" @click="toggleColumn('is_create')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] text-slate-600 dark:text-slate-300 transition">Create</button>
              <button type="button" @click="toggleColumn('is_read')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] text-slate-600 dark:text-slate-300 transition">Read</button>
              <button type="button" @click="toggleColumn('is_edit')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] text-slate-600 dark:text-slate-300 transition">Edit</button>
              <button type="button" @click="toggleColumn('is_delete')" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] text-slate-600 dark:text-slate-300 transition">Delete</button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
              <thead class="bg-slate-50/70 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3">Module Name</th>
                  <th class="px-4 py-3">Path / Table</th>
                  <th class="px-4 py-3 text-center">Row Action</th>
                  <th class="px-4 py-3 text-center">Visible</th>
                  <th class="px-4 py-3 text-center">Create</th>
                  <th class="px-4 py-3 text-center">Read</th>
                  <th class="px-4 py-3 text-center">Edit</th>
                  <th class="px-4 py-3 text-center">Delete</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                <tr v-for="m in modules" :key="m.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                  
                  <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i :class="m.icon || 'bi bi-boxes'" class="text-indigo-500"></i>
                    <span>{{ m.name }}</span>
                  </td>

                  <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                    <span class="font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-[11px]">{{ m.path }}</span>
                  </td>

                  <td class="px-4 py-3 text-center">
                    <button
                      type="button"
                      @click="toggleRow(m.id)"
                      class="px-2 py-1 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded transition"
                    >
                      Toggle Row
                    </button>
                  </td>

                  <!-- Visible -->
                  <td class="px-4 py-3 text-center">
                    <input
                      type="checkbox"
                      v-model="form.roles[m.id].is_visible"
                      :true-value="1"
                      :false-value="0"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                  </td>

                  <!-- Create -->
                  <td class="px-4 py-3 text-center">
                    <input
                      type="checkbox"
                      v-model="form.roles[m.id].is_create"
                      :true-value="1"
                      :false-value="0"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                  </td>

                  <!-- Read -->
                  <td class="px-4 py-3 text-center">
                    <input
                      type="checkbox"
                      v-model="form.roles[m.id].is_read"
                      :true-value="1"
                      :false-value="0"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                  </td>

                  <!-- Edit -->
                  <td class="px-4 py-3 text-center">
                    <input
                      type="checkbox"
                      v-model="form.roles[m.id].is_edit"
                      :true-value="1"
                      :false-value="0"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                  </td>

                  <!-- Delete -->
                  <td class="px-4 py-3 text-center">
                    <input
                      type="checkbox"
                      v-model="form.roles[m.id].is_delete"
                      :true-value="1"
                      :false-value="0"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                  </td>

                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Submit Footer -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <Link
            :href="currentPath.replace('/add', '').replace(/\/edit\/.*/, '')"
            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 disabled:opacity-50 transition flex items-center gap-2"
          >
            <i class="bi bi-floppy"></i>
            <span>Save Privilege Roles</span>
          </button>
        </div>

      </form>

    </div>
  </InerminAppLayout>
</template>
