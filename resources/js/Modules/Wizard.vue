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
  module_type: 'crud',
  name: props.row?.name || '',
  table_name: props.row?.table_name || (props.tables?.[0] || ''),
  icon: props.row?.icon || 'bi bi-boxes',
  path: props.row?.path || '',
  controller: props.row?.controller || '',
  create_menu: true,
})

watch(() => step1Form.value.name, (newName) => {
  if (!props.id && newName) {
    step1Form.value.path = newName.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '')
  }
})

watch(() => step1Form.value.module_type, (newType) => {
  if (newType === 'custom') {
    step1Form.value.table_name = ''
  } else if (!step1Form.value.table_name && props.tables?.length) {
    step1Form.value.table_name = props.tables[0]
  }
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

const submitStep1 = () => {
  router.post(adminPath.value + '/modules/step2', step1Form.value)
}

const submitStep2 = () => {
  router.post(adminPath.value + '/modules/step3', {
    id: props.id || step1Form.value.id,
    columns: step2Cols.value,
  })
}

const submitStep3 = () => {
  router.post(adminPath.value + '/modules/step4', {
    id: props.id || step1Form.value.id,
    forms: step3Forms.value,
  })
}

const submitStep4 = () => {
  router.post(adminPath.value + '/modules/finish', {
    id: props.id || step1Form.value.id,
    roles: step4Roles.value,
  })
}

const addCol = () => {
  step2Cols.value.push({ name: 'field_name', label: 'FIELD NAME', image: false })
}

const removeCol = (idx) => {
  step2Cols.value.splice(idx, 1)
}

const addFormField = () => {
  step3Forms.value.push({ name: 'field_name', label: 'FIELD NAME', type: 'text', required: true, help: '' })
}

const removeFormField = (idx) => {
  step3Forms.value.splice(idx, 1)
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full max-w-5xl mx-auto">
      
      <!-- Wizard Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-stone-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">Step-by-step CRUDBooster Module Generator</p>
        </div>

        <Link
          :href="adminPath + '/modules'"
          class="px-3.5 py-2 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-200 hover:bg-stone-50 font-semibold text-xs rounded-xl shadow-sm transition"
        >
          Cancel & Back
        </Link>
      </div>

      <!-- Step Indicator Bar -->
      <div class="grid grid-cols-4 gap-2 card p-2 rounded-2xl border border-stone-200 dark:border-white/10 shadow-sm text-xs font-bold">
        
        <div
          :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 1 ? 'text-white shadow-md' : (step > 1 ? 'bg-stone-100 dark:bg-white/5 text-[rgb(var(--accent-rgb))]' : 'text-stone-400')]"
          :style="step === 1 ? 'background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)))' : ''"
        >
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">1</span>
          <span>Module Info</span>
        </div>

        <div
          :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 2 ? 'text-white shadow-md' : (step > 2 ? 'bg-stone-100 dark:bg-white/5 text-[rgb(var(--accent-rgb))]' : 'text-stone-400')]"
          :style="step === 2 ? 'background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)))' : ''"
        >
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">2</span>
          <span>Table Columns</span>
        </div>

        <div
          :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 3 ? 'text-white shadow-md' : (step > 3 ? 'bg-stone-100 dark:bg-white/5 text-[rgb(var(--accent-rgb))]' : 'text-stone-400')]"
          :style="step === 3 ? 'background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)))' : ''"
        >
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">3</span>
          <span>Form Fields</span>
        </div>

        <div
          :class="['py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition', step === 4 ? 'text-white shadow-md' : 'text-stone-400']"
          :style="step === 4 ? 'background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)))' : ''"
        >
          <span class="w-5 h-5 rounded-full border border-current flex items-center justify-center text-[10px]">4</span>
          <span>Privileges & Finish</span>
        </div>

      </div>

      <!-- Step 1: Module Info -->
      <div v-if="step === 1" class="card rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="submitStep1" class="space-y-4">
          
          <!-- Module Type Selector -->
          <div>
            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-2">Module Type *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label :class="['p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3', step1Form.module_type === 'crud' ? 'border-[rgb(var(--accent-rgb))] bg-[rgb(var(--accent-rgb))]/5' : 'border-stone-200 dark:border-white/10']">
                <input type="radio" v-model="step1Form.module_type" value="crud" class="mt-0.5 text-[rgb(var(--accent-rgb))] focus:ring-[rgb(var(--accent-rgb))]" />
                <div>
                  <div class="text-xs font-bold text-stone-900 dark:text-white flex items-center gap-1.5">
                    <i class="bi bi-table text-[rgb(var(--accent-rgb))]"></i> Standard CRUD Module
                  </div>
                  <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5">Automated Data Table, Form Fields, Detail view connected to DB table.</p>
                </div>
              </label>

              <label :class="['p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3', step1Form.module_type === 'custom' ? 'border-[rgb(var(--accent-rgb))] bg-[rgb(var(--accent-rgb))]/5' : 'border-stone-200 dark:border-white/10']">
                <input type="radio" v-model="step1Form.module_type" value="custom" class="mt-0.5 text-[rgb(var(--accent-rgb))] focus:ring-[rgb(var(--accent-rgb))]" />
                <div>
                  <div class="text-xs font-bold text-stone-900 dark:text-white flex items-center gap-1.5">
                    <i class="bi bi-code-square text-indigo-500"></i> Custom View Module
                  </div>
                  <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5">Custom Controller + Vue View component scaffold (e.g. Chat, Reports, Tools).</p>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Module Name *</label>
            <input
              v-model="step1Form.name"
              type="text"
              required
              placeholder="e.g. Products, Absen Data, Live Chat"
              class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl px-3.5 py-2 text-xs font-medium text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
            />
          </div>

          <div v-if="step1Form.module_type === 'crud'">
            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Database Table *</label>
            <select
              v-model="step1Form.table_name"
              :required="step1Form.module_type === 'crud'"
              class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl px-3.5 py-2 text-xs font-medium text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
            >
              <option v-for="tbl in tables" :key="tbl" :value="tbl">{{ tbl }}</option>
            </select>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">Bootstrap Icon Class</label>
              <input
                v-model="step1Form.icon"
                type="text"
                placeholder="e.g. bi bi-box-seam"
                class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl px-3.5 py-2 text-xs font-medium text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 mb-1">URL Slug / Path</label>
              <input
                v-model="step1Form.path"
                type="text"
                placeholder="Auto-generated if empty (e.g. products)"
                class="w-full bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl px-3.5 py-2 text-xs font-medium text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
              />
            </div>
          </div>

          <div v-if="!id" class="flex items-center gap-2 pt-2">
            <input
              type="checkbox"
              id="create_menu"
              v-model="step1Form.create_menu"
              class="rounded border-stone-300 text-[rgb(var(--accent-rgb))] focus:ring-[rgb(var(--accent-rgb))]"
            />
            <label for="create_menu" class="text-xs font-bold text-stone-700 dark:text-stone-300">Auto Create Menu Entry for this Module</label>
          </div>

          <div class="pt-4 border-t border-stone-200 dark:border-white/10 flex justify-end">
            <button
              type="submit"
              class="px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2"
              style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
            >
              <span>Save & Proceed to Step 2</span>
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>

        </form>
      </div>

      <!-- Step 2: Display Columns -->
      <div v-if="step === 2" class="card rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-display font-bold text-sm text-stone-900 dark:text-white">Configure Table Datagrid Columns</h3>
          <button @click="addCol" class="px-3 py-1.5 bg-stone-100 dark:bg-white/10 text-stone-700 dark:text-stone-200 font-bold text-xs rounded-lg hover:bg-stone-200 transition">
            + Add Column
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(col, idx) in step2Cols" :key="idx" class="flex items-center gap-3 p-3 bg-stone-50 dark:bg-white/[0.02] rounded-xl border border-stone-200 dark:border-white/10">
            <div class="w-1/3">
              <label class="block text-[11px] font-bold text-stone-400 mb-0.5">Field Name</label>
              <input v-model="col.name" type="text" class="w-full bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-900 dark:text-stone-100" />
            </div>

            <div class="w-1/3">
              <label class="block text-[11px] font-bold text-stone-400 mb-0.5">Display Label</label>
              <input v-model="col.label" type="text" class="w-full bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-900 dark:text-stone-100" />
            </div>

            <div class="flex items-center gap-2 pt-4">
              <input type="checkbox" :id="'img_'+idx" v-model="col.image" class="rounded border-stone-300 text-[rgb(var(--accent-rgb))]" />
              <label :for="'img_'+idx" class="text-xs font-semibold text-stone-700 dark:text-stone-300">Is Image</label>
            </div>

            <button @click="removeCol(idx)" class="ml-auto p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <div class="pt-4 border-t border-stone-200 dark:border-white/10 flex justify-end">
          <button
            @click="submitStep2"
            class="px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
          >
            <span>Save & Proceed to Step 3</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Step 3: Form Fields -->
      <div v-if="step === 3" class="card rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-display font-bold text-sm text-stone-900 dark:text-white">Configure Add & Edit Form Fields</h3>
          <button @click="addFormField" class="px-3 py-1.5 bg-stone-100 dark:bg-white/10 text-stone-700 dark:text-stone-200 font-bold text-xs rounded-lg hover:bg-stone-200 transition">
            + Add Form Field
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(form, idx) in step3Forms" :key="idx" class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-stone-50 dark:bg-white/[0.02] rounded-xl border border-stone-200 dark:border-white/10">
            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-stone-400 mb-0.5">Field Name</label>
              <input v-model="form.name" type="text" class="w-full bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-900 dark:text-stone-100" />
            </div>

            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-stone-400 mb-0.5">Field Label</label>
              <input v-model="form.label" type="text" class="w-full bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-900 dark:text-stone-100" />
            </div>

            <div class="w-full sm:w-1/4">
              <label class="block text-[11px] font-bold text-stone-400 mb-0.5">Input Type</label>
              <select v-model="form.type" class="w-full bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-900 dark:text-stone-100">
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
              <input type="checkbox" :id="'req_'+idx" v-model="form.required" class="rounded border-stone-300 text-[rgb(var(--accent-rgb))]" />
              <label :for="'req_'+idx" class="text-xs font-semibold text-stone-700 dark:text-stone-300">Required</label>
            </div>

            <button @click="removeFormField(idx)" class="ml-auto p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <div class="pt-4 border-t border-stone-200 dark:border-white/10 flex justify-end">
          <button
            @click="submitStep3"
            class="px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
          >
            <span>Save & Proceed to Step 4</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Step 4: Privileges & Finish -->
      <div v-if="step === 4" class="card rounded-2xl p-6 shadow-sm space-y-4">
        <h3 class="font-display font-bold text-sm text-stone-900 dark:text-white">Configure Privilege Permissions</h3>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-stone-200 dark:border-white/10 bg-stone-50 dark:bg-white/[0.02] text-[10px] font-bold text-stone-400 uppercase">
                <th class="p-3">Privilege Name</th>
                <th class="p-3 text-center">Is Visible</th>
                <th class="p-3 text-center">Can Create</th>
                <th class="p-3 text-center">Can Read</th>
                <th class="p-3 text-center">Can Edit</th>
                <th class="p-3 text-center">Can Delete</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-stone-100 dark:divide-white/5">
              <tr v-for="priv in privileges" :key="priv.id">
                <td class="p-3 font-bold text-stone-900 dark:text-white">{{ priv.name }}</td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_visible" class="rounded text-[rgb(var(--accent-rgb))]" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_create" class="rounded text-[rgb(var(--accent-rgb))]" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_read" class="rounded text-[rgb(var(--accent-rgb))]" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_edit" class="rounded text-[rgb(var(--accent-rgb))]" /></td>
                <td class="p-3 text-center"><input type="checkbox" v-model="getRole(priv.id).is_delete" class="rounded text-[rgb(var(--accent-rgb))]" /></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-4 border-t border-stone-200 dark:border-white/10 flex justify-end">
          <button
            @click="submitStep4"
            class="px-6 py-2.5 text-white font-bold text-xs rounded-xl shadow-lg transition-transform hover:scale-105 flex items-center gap-2"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
          >
            <i class="bi bi-check-lg text-base"></i>
            <span>Finish Module Generation</span>
          </button>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
