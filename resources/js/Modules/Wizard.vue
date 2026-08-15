<script setup>
import { ref, computed, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator'))

const props = defineProps({
  page_title: String,
  step: Number,
  id: [Number, String],
  row: Object,
  tables: Array,
  columns: Array,
  privileges: Array,
})

// Step 1 Form State
const step1Form = ref({
  id: props.id || 0,
  name: props.row?.name || '',
  table_name: props.row?.table_name || (props.tables?.[0] || ''),
  icon: props.row?.icon || 'bi bi-boxes',
  path: props.row?.path || '',
  controller: props.row?.controller || '',
  create_menu: true,
})

// Step 2 Columns State
const step2Cols = ref([])

// Step 3 Forms State
const step3Forms = ref([])

// Step 4 Privileges Roles State
const step4Roles = ref({})

watch(() => props.columns, (newCols) => {
  if (newCols && newCols.length) {
    step2Cols.value = newCols.map(col => ({
      name: col,
      label: col.replace(/_/g, ' ').toUpperCase(),
      image: col.includes('image') || col.includes('photo') || col.includes('avatar'),
    }))

    step3Forms.value = newCols.filter(c => c !== 'id' && c !== 'created_at' && c !== 'updated_at').map(col => ({
      name: col,
      label: col.replace(/_/g, ' ').toUpperCase(),
      type: col.includes('email') ? 'email' : (col.includes('password') ? 'password' : (col.includes('photo') || col.includes('image') ? 'upload' : 'text')),
      required: !col.includes('photo') && !col.includes('image') && !col.includes('deleted'),
      help: '',
    }))
  }
}, { immediate: true })

watch(() => props.privileges, (newPrivs) => {
  if (newPrivs && newPrivs.length) {
    newPrivs.forEach(p => {
      if (!step4Roles.value[p.id]) {
        step4Roles.value[p.id] = {
          is_visible: true,
          is_create: true,
          is_read: true,
          is_edit: true,
          is_delete: true,
        }
      }
    })
  }
}, { immediate: true })

const getRole = (privId) => {
  if (!step4Roles.value[privId]) {
    step4Roles.value[privId] = {
      is_visible: true,
      is_create: true,
      is_read: true,
      is_edit: true,
      is_delete: true,
    }
  }
  return step4Roles.value[privId]
}

const addCol = () => {
  step2Cols.value.push({ name: '', label: '', image: false })
}
const removeCol = (idx) => {
  step2Cols.value.splice(idx, 1)
}

const addFormField = () => {
  step3Forms.value.push({ name: '', label: '', type: 'text', required: false, help: '' })
}
const removeFormField = (idx) => {
  step3Forms.value.splice(idx, 1)
}

const submitStep1 = () => {
  router.post(adminPath.value + '/modules/step2', step1Form.value)
}

const submitStep2 = () => {
  router.post(adminPath.value + '/modules/step3', { id: props.id, columns: step2Cols.value })
}

const submitStep3 = () => {
  router.post(adminPath.value + '/modules/step4', { id: props.id, forms: step3Forms.value })
}

const submitStep4 = () => {
  router.post(adminPath.value + '/modules/finish', { id: props.id, privileges: step4Roles.value })
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-5xl mx-auto space-y-6 font-sans w-full">
      
      <!-- Wizard Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Step-by-step CRUDBooster Module Generator</p>
        </div>

        <Link
          href="/administrator/modules"
          class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 font-semibold text-xs rounded-xl shadow-sm transition"
        >
          Cancel & Back
        </Link>
      </div>

      <!-- Step Indicator Bar -->
      <div class="grid grid-cols-4 gap-2 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm text-xs font-bold">
        
        <div :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 1 ? 'bg-indigo-600 text-white shadow-md' : (step > 1 ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-slate-400')]">
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">1</span>
          <span>Module Info</span>
        </div>

        <div :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 2 ? 'bg-indigo-600 text-white shadow-md' : (step > 2 ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-slate-400')]">
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">2</span>
          <span>Table Columns</span>
        </div>

        <div :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 3 ? 'bg-indigo-600 text-white shadow-md' : (step > 3 ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' : 'text-slate-400')]">
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">3</span>
          <span>Form Fields</span>
        </div>

        <div :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 4 ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400']">
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">4</span>
          <span>Privileges & Finish</span>
        </div>

      </div>

      <!-- Step 1: Module Info -->
      <div v-if="step === 1" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="submitStep1" class="space-y-4">
          
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Module Name *</label>
            <input
              v-model="step1Form.name"
              type="text"
              required
              placeholder="e.g. Products, Absen Data"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Database Table *</label>
            <select
              v-model="step1Form.table_name"
              required
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100"
            >
              <option v-for="t in tables" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Icon (Bootstrap Icon Class)</label>
            <input
              v-model="step1Form.icon"
              type="text"
              placeholder="bi bi-boxes, bi bi-grid, bi bi-tags"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Path Slug (Optional)</label>
            <input
              v-model="step1Form.path"
              type="text"
              placeholder="Auto-generated if empty (e.g. products)"
              class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 dark:text-slate-100"
            />
          </div>

          <div v-if="!id" class="flex items-center gap-2 pt-2">
            <input
              type="checkbox"
              id="create_menu"
              v-model="step1Form.create_menu"
              class="rounded border-slate-300 text-indigo-600"
            />
            <label for="create_menu" class="text-xs font-bold text-slate-700 dark:text-slate-300">Auto Create Menu Entry for this Module</label>
          </div>

          <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
            <button
              type="submit"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2"
            >
              <span>Save & Proceed to Step 2</span>
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>

        </form>
      </div>

      <!-- Step 2: Display Columns -->
      <div v-if="step === 2" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">Configure Table Datagrid Columns</h3>
          <button @click="addCol" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-lg">
            + Add Column
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(col, idx) in step2Cols" :key="idx" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="w-1/3">
              <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Field Name</label>
              <input v-model="col.name" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100" />
            </div>

            <div class="w-1/3">
              <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Display Label</label>
              <input v-model="col.label" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100" />
            </div>

            <div class="flex items-center gap-2 pt-4">
              <input type="checkbox" :id="'img_'+idx" v-model="col.image" class="rounded border-slate-300 text-indigo-600" />
              <label :for="'img_'+idx" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Is Image</label>
            </div>

            <button @click="removeCol(idx)" class="ml-auto p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
          <button @click="submitStep2" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
            <span>Save & Proceed to Step 3</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Step 3: Form Fields -->
      <div v-if="step === 3" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">Configure Add & Edit Form Fields</h3>
          <button @click="addFormField" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-lg">
            + Add Form Field
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(form, idx) in step3Forms" :key="idx" class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Field Name</label>
              <input v-model="form.name" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100" />
            </div>

            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Field Label</label>
              <input v-model="form.label" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100" />
            </div>

            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-slate-500 mb-0.5">Input Type</label>
              <select v-model="form.type" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 dark:text-slate-100">
                <option value="text">Text</option>
                <option value="email">Email</option>
                <option value="password">Password</option>
                <option value="number">Number</option>
                <option value="textarea">Textarea</option>
                <option value="select">Select Dropdown</option>
                <option value="upload">File / Image Upload</option>
                <option value="date">Date</option>
                <option value="datetime">Datetime</option>
              </select>
            </div>

            <div class="flex items-center gap-2 pt-4">
              <input type="checkbox" :id="'req_'+idx" v-model="form.required" class="rounded border-slate-300 text-indigo-600" />
              <label :for="'req_'+idx" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Required</label>
            </div>

            <button @click="removeFormField(idx)" class="ml-auto p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
          <button @click="submitStep3" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
            <span>Save & Proceed to Step 4</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Step 4: Privileges & Finish -->
      <div v-if="step === 4" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Configure Privilege Permissions</h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">
                <th class="p-3">Privilege Name</th>
                <th class="p-3 text-center">Is Visible</th>
                <th class="p-3 text-center">Can Create</th>
                <th class="p-3 text-center">Can Read</th>
                <th class="p-3 text-center">Can Edit</th>
                <th class="p-3 text-center">Can Delete</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="priv in privileges" :key="priv.id">
                <td class="p-3 font-bold text-slate-900 dark:text-white">{{ priv.name }}</td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_visible" class="rounded text-indigo-600" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_create" class="rounded text-indigo-600" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_read" class="rounded text-indigo-600" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_edit" class="rounded text-indigo-600" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_delete" class="rounded text-indigo-600" /></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
          <button @click="submitStep4" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <i class="bi bi-check-lg text-base"></i>
            <span>Finish Module Generation</span>
          </button>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
