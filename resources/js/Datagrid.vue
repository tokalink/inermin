<script setup>
import { ref, computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  table_name: String,
  primary_key: String,
  columns: Array,
  data: Object,
  filters: Object,
  permissions: Object,
  sub_module: Array,
  addaction: Array,
  index_button: Array,
  button_selected: Array,
  index_statistic: Array,
  alerts: Array,
  parent_info: Object,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator'))
const currentPath = computed(() => page.url.split('?')[0])

const getSubModuleUrl = (sub, item) => {
  const pCol = sub.parent_columns || props.primary_key
  const pVal = item[pCol] || item[props.primary_key] || ''
  const fKey = sub.foreign_key || 'parent_id'
  const path = sub.path || ''
  
  return `${adminPath.value}/${path}?parent_table=${props.table_name}&parent_columns=${pCol}&parent_columns_alias=${encodeURIComponent(pCol)}&parent_id=${item[props.primary_key]}&parent_value=${encodeURIComponent(pVal)}&foreign_key=${fKey}`
}

const searchQuery = ref(props.filters?.q || '')
const currentOrderby = ref(props.filters?.orderby || '')
const currentLimit = ref(props.filters?.limit || 20)

// CRUDBooster Advanced Filter Drawer, Import, & Export Modal State
const isFilterDrawerOpen = ref(false)
const isImportModalOpen = ref(false)
const isExportModalOpen = ref(false)
const filterConditions = ref([])

const exportFormat = ref('xlsx')
const exportPaperSize = ref('a4')
const exportOrientation = ref('landscape')
const exportFilename = ref('')
const exportSelectedCols = ref(props.columns ? props.columns.map(c => c.name) : [])

const selectAllExportCols = () => {
  exportSelectedCols.value = props.columns ? props.columns.map(c => c.name) : []
}

const deselectAllExportCols = () => {
  exportSelectedCols.value = []
}

// Initialize active filters from props.filters.filter_column
if (props.filters?.filter_column && typeof props.filters.filter_column === 'object') {
  Object.keys(props.filters.filter_column).forEach(colName => {
    const item = props.filters.filter_column[colName]
    filterConditions.value.push({
      column: colName,
      type: item.type || 'like',
      value: item.value || ''
    })
  })
}

if (!filterConditions.value.length && props.columns && props.columns.length) {
  filterConditions.value.push({
    column: props.columns[0].name,
    type: 'like',
    value: ''
  })
}

const addFilterRow = () => {
  if (props.columns && props.columns.length) {
    filterConditions.value.push({
      column: props.columns[0].name,
      type: 'like',
      value: ''
    })
  }
}

const removeFilterRow = (index) => {
  filterConditions.value.splice(index, 1)
}

const activeFilterCount = computed(() => {
  return filterConditions.value.filter(f => f.column && (f.value !== '' || f.type === 'empty')).length
})

const applyFilters = () => {
  const filterObject = {}
  filterConditions.value.forEach(f => {
    if (f.column && (f.value !== '' || f.type === 'empty')) {
      filterObject[f.column] = {
        type: f.type,
        value: f.value
      }
    }
  })

  router.get(currentPath.value, {
    q: searchQuery.value,
    orderby: currentOrderby.value,
    limit: currentLimit.value,
    filter_column: filterObject
  }, { preserveState: true })

  isFilterDrawerOpen.value = false
}

const resetFilters = () => {
  filterConditions.value = []
  if (props.columns && props.columns.length) {
    filterConditions.value.push({
      column: props.columns[0].name,
      type: 'like',
      value: ''
    })
  }

  router.get(currentPath.value, {
    q: searchQuery.value,
    orderby: currentOrderby.value,
    limit: currentLimit.value
  }, { preserveState: true })

  isFilterDrawerOpen.value = false
}

// Custom Add Action URL and Condition Parser
const getActionUrl = (urlTemplate, row) => {
  if (!urlTemplate) return '#'
  let url = urlTemplate
  Object.keys(row).forEach(key => {
    if (!key.startsWith('_raw_')) {
      const rawVal = row['_raw_' + key] !== undefined && row['_raw_' + key] !== null ? row['_raw_' + key] : row[key]
      url = url.replace(new RegExp(`\\[${key}\\]`, 'g'), rawVal !== undefined && rawVal !== null ? rawVal : '')
    }
  })
  return url
}

const shouldShowAddAction = (action, row) => {
  if (!action.showIf) return true
  let condition = action.showIf
  Object.keys(row).forEach(key => {
    condition = condition.replace(new RegExp(`\\[${key}\\]`, 'g'), JSON.stringify(row[key]))
  })
  try {
    return new Function('return (' + condition + ')')()
  } catch (e) {
    return true
  }
}

// Bulk Select State
const selectedRows = ref([])
const selectAll = ref(false)

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedRows.value = props.data.data.map(item => item[props.primary_key])
  } else {
    selectedRows.value = []
  }
}

const handleSearch = () => {
  const filterObject = {}
  filterConditions.value.forEach(f => {
    if (f.column && (f.value !== '' || f.type === 'empty')) {
      filterObject[f.column] = {
        type: f.type,
        value: f.value
      }
    }
  })

  router.get(currentPath.value, {
    q: searchQuery.value,
    orderby: currentOrderby.value,
    limit: currentLimit.value,
    filter_column: Object.keys(filterObject).length ? filterObject : undefined
  }, { preserveState: true })
}

const clearSearch = () => {
  searchQuery.value = ''
  handleSearch()
}

const handleSort = (columnName) => {
  let direction = 'asc'
  if (currentOrderby.value.startsWith(columnName)) {
    direction = currentOrderby.value.endsWith('asc') ? 'desc' : 'asc'
  }
  currentOrderby.value = `${columnName},${direction}`
  handleSearch()
}

const executeBulkAction = (actionName) => {
  if (!selectedRows.value.length) {
    alert('Please select at least one row.')
    return
  }
  if (confirm(`Are you sure you want to execute action "${actionName}" on selected items?`)) {
    router.post(currentPath.value + '/action-selected', {
      button_name: actionName,
      id_selected: selectedRows.value
    })
  }
}

const deleteRow = (id) => {
  if (confirm('Are you sure you want to delete this record?')) {
    router.get(currentPath.value + '/delete/' + id)
  }
}

const isImage = (val, col = null) => {
  if (typeof val !== 'string' || !val) return false
  if (col && col.image) return true

  const colName = col ? (col.name || '').toLowerCase() : ''
  const isImageColName = ['logo', 'image', 'photo', 'avatar', 'picture', 'thumbnail', 'banner', 'foto'].some(k => colName.includes(k))
  const isImageFileExt = val.match(/\.(jpeg|jpg|gif|png|webp|svg)(\?.*)?$/i) != null
  const isStorageUpload = val.startsWith('storage/') || val.startsWith('/storage/')

  if (isImageFileExt) return true
  if (isImageColName && (isStorageUpload || val.startsWith('http'))) return true

  return false
}

const formatImageUrl = (val) => {
  if (typeof val !== 'string' || !val) return ''
  if (val.startsWith('http://') || val.startsWith('https://')) return val
  return val.startsWith('/') ? val : '/' + val
}

const filterOperators = [
  { label: 'Contains (like)', value: 'like' },
  { label: 'Equals (=)', value: '=' },
  { label: 'Not Equals (!=)', value: '!=' },
  { label: 'Does Not Contain', value: 'not like' },
  { label: 'Greater Than (>)', value: '>' },
  { label: 'Less Than (<)', value: '<' },
  { label: 'Greater / Equals (>=)', value: '>=' },
  { label: 'Less / Equals (<=)', value: '<=' },
  { label: 'In List (comma separated)', value: 'in' },
  { label: 'Is Empty / Null', value: 'empty' },
]

// Dropdown Action State per Row
const activeDropdownRow = ref(null)
const toggleDropdown = (id) => {
  activeDropdownRow.value = activeDropdownRow.value === id ? null : id
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans">
      
      <!-- Page Header & Main Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage records and data listing for <span class="font-bold text-slate-700 dark:text-slate-300">{{ table_name }}</span></p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
          
          <!-- Crisp Clean Add Button -->
          <Link
            v-if="permissions.can_add"
            :href="currentPath + '/add'"
            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-2 shadow-lg transition-transform hover:scale-105 active:scale-95"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
          >
            <i class="bi bi-plus-circle-fill text-sm"></i>
            <span>Add {{ page_title }}</span>
          </Link>

          <!-- Custom Index Buttons -->
          <template v-if="index_button && index_button.length">
            <template v-for="(btn, idx) in index_button" :key="idx">
              <a
                :href="btn.url"
                :target="btn.target || '_self'"
                :class="[
                  'inline-flex items-center gap-2 px-4 py-2.5 font-bold text-xs rounded-xl shadow-xs transition border',
                  btn.color === 'primary' || btn.color === 'indigo'
                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white border-indigo-600'
                    : btn.color === 'success' || btn.color === 'emerald'
                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600'
                    : btn.color === 'danger' || btn.color === 'rose'
                    ? 'bg-rose-600 hover:bg-rose-700 text-white border-rose-600'
                    : btn.color === 'warning' || btn.color === 'amber'
                    ? 'bg-amber-500 hover:bg-amber-600 text-white border-amber-500'
                    : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'
                ]"
              >
                <i v-if="btn.icon" :class="[btn.icon, 'text-sm']"></i>
                <span>{{ btn.label }}</span>
              </a>
            </template>
          </template>

          <!-- Advanced Filter Button -->
          <button
            v-if="permissions.can_filter !== false"
            @click="isFilterDrawerOpen = true"
            :class="[
              'inline-flex items-center gap-2 px-4 py-2.5 font-bold text-xs rounded-xl border transition shadow-xs',
              activeFilterCount > 0
                ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 border-indigo-300 dark:border-indigo-700'
                : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'
            ]"
          >
            <i class="bi bi-funnel-fill text-sm"></i>
            <span>Filter</span>
            <span v-if="activeFilterCount > 0" class="px-1.5 py-0.5 text-[10px] bg-indigo-600 text-white rounded-full font-bold">
              {{ activeFilterCount }}
            </span>
          </button>

          <!-- Export Data Button -->
          <button
            v-if="permissions.can_export"
            @click="isExportModalOpen = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-200 hover:bg-stone-50 dark:hover:bg-stone-800 font-bold text-xs rounded-xl shadow-xs transition"
          >
            <i class="bi bi-download text-indigo-500"></i>
            <span>Export Data</span>
          </button>

          <!-- Import Data Button -->
          <button
            v-if="permissions.can_import"
            @click="isImportModalOpen = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-stone-900 border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-200 hover:bg-stone-50 dark:hover:bg-stone-800 font-bold text-xs rounded-xl shadow-xs transition"
          >
            <i class="bi bi-upload text-emerald-500"></i>
            <span>Import Data</span>
          </button>
        </div>
      </div>

      <!-- Advanced Export Data Modal -->
      <Transition name="fade">
        <div v-if="isExportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div class="card w-full max-w-lg rounded-2xl p-6 shadow-2xl space-y-4 border border-stone-200 dark:border-white/10 max-h-[90vh] flex flex-col">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-white/5 pb-3 shrink-0">
              <h3 class="font-bold text-base text-stone-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-file-earmark-pdf text-indigo-500"></i>
                <span>Export Data Options</span>
              </h3>
              <button @click="isExportModalOpen = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <!-- Form -->
            <form :action="currentPath + '/export-data'" method="POST" target="_blank" class="space-y-4 overflow-y-auto pr-1 custom-scrollbar flex-1">
              <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />
              <input type="hidden" name="q" :value="searchQuery" />

              <!-- File Format Selector -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-stone-700 dark:text-stone-300">File Format</label>
                <div class="grid grid-cols-3 gap-2">
                  <label :class="['flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition', exportFormat === 'xlsx' ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-stone-200 dark:border-white/10 text-stone-600 dark:text-stone-400']">
                    <input type="radio" name="fileformat" value="xlsx" v-model="exportFormat" class="sr-only" />
                    <i class="bi bi-file-earmark-excel text-base"></i>
                    <span>Excel (.xlsx)</span>
                  </label>
                  
                  <label :class="['flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition', exportFormat === 'pdf' ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-500 text-rose-600 dark:text-rose-400' : 'border-stone-200 dark:border-white/10 text-stone-600 dark:text-stone-400']">
                    <input type="radio" name="fileformat" value="pdf" v-model="exportFormat" class="sr-only" />
                    <i class="bi bi-file-earmark-pdf text-base"></i>
                    <span>PDF Document</span>
                  </label>

                  <label :class="['flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition', exportFormat === 'csv' ? 'bg-amber-50 dark:bg-amber-950/60 border-amber-500 text-amber-600 dark:text-amber-400' : 'border-stone-200 dark:border-white/10 text-stone-600 dark:text-stone-400']">
                    <input type="radio" name="fileformat" value="csv" v-model="exportFormat" class="sr-only" />
                    <i class="bi bi-file-earmark-text text-base"></i>
                    <span>CSV File</span>
                  </label>
                </div>
              </div>

              <!-- PDF Specific Options (Paper Size & Orientation) -->
              <Transition name="fade">
                <div v-if="exportFormat === 'pdf'" class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-stone-50 dark:bg-white/[0.03] border border-stone-200/60 dark:border-white/5">
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-stone-600 dark:text-stone-400">Paper Size</label>
                    <select name="paper_size" v-model="exportPaperSize" class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-800 dark:text-stone-200 font-medium">
                      <option value="a4">A4 (Standard)</option>
                      <option value="legal">Legal (Long)</option>
                      <option value="letter">Letter</option>
                    </select>
                  </div>

                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-stone-600 dark:text-stone-400">Orientation</label>
                    <select name="page_orientation" v-model="exportOrientation" class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-2.5 py-1.5 text-xs text-stone-800 dark:text-stone-200 font-medium">
                      <option value="landscape">Landscape (Horizontal Wide)</option>
                      <option value="portrait">Portrait (Vertical)</option>
                    </select>
                  </div>
                </div>
              </Transition>

              <!-- Filename Input -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Custom Filename (Optional)</label>
                <input
                  type="text"
                  name="filename"
                  v-model="exportFilename"
                  :placeholder="table_name + '_export_' + new Date().toISOString().slice(0,10)"
                  class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </div>

              <!-- Column Selector Checkboxes -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-bold text-stone-700 dark:text-stone-300">
                    Select Columns to Export ({{ exportSelectedCols.length }} of {{ columns.length }})
                  </label>
                  <div class="flex items-center gap-2 text-[11px] font-bold">
                    <button type="button" @click="selectAllExportCols" class="text-indigo-600 dark:text-indigo-400 hover:underline">Select All</button>
                    <span class="text-stone-300 dark:text-stone-700">&bull;</span>
                    <button type="button" @click="deselectAllExportCols" class="text-rose-600 dark:text-rose-400 hover:underline">Deselect All</button>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 rounded-xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200/60 dark:border-white/5 custom-scrollbar">
                  <label
                    v-for="col in columns"
                    :key="col.name"
                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-stone-100 dark:hover:bg-white/5 cursor-pointer text-xs font-medium text-stone-800 dark:text-stone-200 select-none"
                  >
                    <input
                      type="checkbox"
                      name="columns[]"
                      :value="col.name"
                      v-model="exportSelectedCols"
                      class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                    />
                    <span class="truncate">{{ col.label || col.name }}</span>
                  </label>
                </div>
              </div>

              <!-- Action Footer -->
              <div class="flex justify-end gap-2 pt-3 border-t border-stone-100 dark:border-white/5 shrink-0">
                <button type="button" @click="isExportModalOpen = false" class="px-4 py-2 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 rounded-xl">
                  Cancel
                </button>
                <button
                  type="submit"
                  @click="isExportModalOpen = false"
                  :disabled="exportSelectedCols.length === 0"
                  class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 rounded-xl shadow-md flex items-center gap-2"
                >
                  <i class="bi bi-download"></i>
                  <span>Download {{ exportFormat.toUpperCase() }}</span>
                </button>
              </div>
            </form>

          </div>
        </div>
      </Transition>

      <!-- Import Data Modal -->
      <Transition name="fade">
        <div v-if="isImportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div class="card w-full max-w-md rounded-2xl p-6 shadow-2xl space-y-4 border border-stone-200 dark:border-white/10">
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-white/5 pb-3">
              <h3 class="font-bold text-base text-stone-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-file-earmark-excel text-emerald-500"></i>
                <span>Import Data (.xlsx / .csv)</span>
              </h3>
              <button @click="isImportModalOpen = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-white">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <form :action="currentPath + '/import-data'" method="POST" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Choose Excel File (.xlsx / .csv)</label>
                <input type="file" name="userfile" accept=".xlsx, .xls, .csv" required class="block w-full text-xs text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-950/50 dark:file:text-emerald-400 cursor-pointer" />
              </div>

              <div class="flex justify-end gap-2 pt-3 border-t border-stone-100 dark:border-white/5">
                <button type="button" @click="isImportModalOpen = false" class="px-4 py-2 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 rounded-xl">
                  Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md">
                  Start Import
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>

      <!-- Active Filters Display Pills Bar -->
      <div v-if="activeFilterCount > 0" class="flex items-center gap-2 flex-wrap bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/60 p-3 rounded-2xl">
        <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
          <i class="bi bi-funnel text-xs"></i> Active Filters:
        </span>
        <template v-for="f in filterConditions" :key="f.column">
          <div v-if="f.column && (f.value !== '' || f.type === 'empty')" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 font-semibold text-xs rounded-lg border border-indigo-200 dark:border-indigo-800 shadow-2xs">
            <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">{{ f.column }}</span>
            <span class="text-[10px] font-bold uppercase text-slate-400">{{ f.type }}</span>
            <span class="font-bold text-slate-800 dark:text-slate-100">{{ f.type === 'empty' ? 'NULL' : f.value }}</span>
          </div>
        </template>
        <button @click="resetFilters" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline ml-auto">
          Clear All Filters
        </button>
      </div>

      <!-- Sub-Module Parent Info Context Banner -->
      <div v-if="parent_info" class="p-4 bg-indigo-500/10 border border-indigo-500/30 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-lg font-bold shadow-sm shrink-0">
            <i class="bi bi-diagram-3-fill"></i>
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">Sub-Module Data View</span>
              <span class="text-xs font-bold bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 px-2 py-0.5 rounded-md font-mono border border-indigo-500/30">
                {{ parent_info.parent_columns_alias || 'Parent' }}: {{ parent_info.parent_value }}
              </span>
            </div>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5 font-medium">
              Filtered child records where <code class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ parent_info.foreign_key }}</code> = #{{ parent_info.parent_id }}
            </p>
          </div>
        </div>

        <Link
          :href="adminPath + '/' + (parent_info.parent_table || '')"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-2 self-start sm:self-auto"
        >
          <i class="bi bi-arrow-left"></i>
          <span>Back to Parent List</span>
        </Link>
      </div>

      <!-- Search & Per Page Toolbar Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Search Bar Input -->
        <div class="relative w-full sm:w-80">
          <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search records... (Press Enter)"
            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-xl pl-9 pr-9 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 focus:outline-none transition"
          />
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs"
          >
            <i class="bi bi-x-circle-fill"></i>
          </button>
        </div>

        <!-- Right Options (Limit & Bulk Actions) -->
        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
          
          <!-- Bulk Action Options -->
          <div v-if="permissions.can_bulk_action && selectedRows.length" class="flex items-center gap-2">
            <!-- Custom Button Selected Actions -->
            <template v-if="button_selected && button_selected.length">
              <button
                v-for="(bSelect, bIdx) in button_selected"
                :key="bIdx"
                @click="executeBulkAction(bSelect.name)"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 text-xs font-bold rounded-xl transition"
              >
                <i v-if="bSelect.icon" :class="[bSelect.icon]"></i>
                <span>{{ bSelect.label }}</span>
              </button>
            </template>

            <!-- Bulk Delete Action -->
            <button
              @click="executeBulkAction('delete')"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 text-xs font-bold rounded-xl transition"
            >
              <i class="bi bi-trash3-fill"></i>
              <span>Delete Selected ({{ selectedRows.length }})</span>
            </button>
          </div>

          <!-- Per Page Limit -->
          <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
            <span>Show</span>
            <select
              v-model="currentLimit"
              @change="handleSearch"
              class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>

        </div>

      </div>

      <!-- Main Data Table Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider font-extrabold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
              <tr>
                <th v-if="permissions.can_bulk_action" class="px-4 py-3.5 w-10 text-center">
                  <input
                    type="checkbox"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                    class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                  />
                </th>

                <th
                  v-for="col in columns"
                  :key="col.name"
                  @click="handleSort(col.name)"
                  class="px-4 py-3.5 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition select-none"
                >
                  <div class="flex items-center gap-1.5">
                    <span>{{ col.label }}</span>
                    <i class="bi bi-arrow-down-up text-[10px] opacity-40"></i>
                  </div>
                </th>

                <th class="px-4 py-3.5 text-right">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
              <tr
                v-for="item in data.data"
                :key="item[primary_key]"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition duration-150"
              >
                <!-- Bulk Checkbox -->
                <td v-if="permissions.can_bulk_action" class="px-4 py-3.5 text-center">
                  <input
                    type="checkbox"
                    :value="item[primary_key]"
                    v-model="selectedRows"
                    class="w-4 h-4 accent-indigo-600 rounded cursor-pointer"
                  />
                </td>

                <!-- Data Columns -->
                <td v-for="col in columns" :key="col.name" class="px-4 py-3.5">
                  <template v-if="isImage(item[col.name], col)">
                    <a :href="formatImageUrl(item[col.name])" target="_blank">
                      <img :src="formatImageUrl(item[col.name])" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-xs hover:scale-105 transition" alt="Thumbnail" />
                    </a>
                  </template>
                  <template v-else-if="col.name === 'id' || col.name === primary_key">
                    <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-lg border border-indigo-200/40 dark:border-indigo-800/40">
                      #{{ item[col.name] }}
                    </span>
                  </template>
                  <template v-else-if="typeof item[col.name] === 'string' && (item[col.name].includes('<') && item[col.name].includes('>'))">
                    <span v-html="item[col.name]"></span>
                  </template>
                  <template v-else>
                    <span class="text-slate-800 dark:text-slate-200">{{ item[col.name] !== null ? item[col.name] : '-' }}</span>
                  </template>
                </td>

                <!-- Action Buttons Render according to button_action_style -->
                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                  
                  <!-- Dropdown Action Style -->
                  <div v-if="permissions.button_action_style === 'dropdown'" class="relative inline-block text-left">
                    <button
                      @click="toggleDropdown(item[primary_key])"
                      class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition inline-flex items-center gap-1.5"
                    >
                      <span>Action</span>
                      <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>

                    <div
                      v-if="activeDropdownRow === item[primary_key]"
                      @click="activeDropdownRow = null"
                      class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 z-40 text-left space-y-1"
                    >
                      <!-- Custom Add Actions in Dropdown -->
                      <template v-for="(act, idx) in addaction" :key="idx">
                        <a
                          v-if="shouldShowAddAction(act, item)"
                          :href="getActionUrl(act.url, item)"
                          :target="act.target || '_self'"
                          class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 hover:text-indigo-600 transition"
                        >
                          <i v-if="act.icon" :class="[act.icon]"></i>
                          <span>{{ act.label }}</span>
                        </a>
                      </template>

                      <Link
                        v-if="permissions.can_detail"
                        :href="currentPath + '/detail/' + item[primary_key]"
                        class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 hover:text-indigo-600 transition"
                      >
                        <i class="bi bi-eye text-indigo-500"></i>
                        <span>Detail</span>
                      </Link>

                      <Link
                        v-if="permissions.can_edit"
                        :href="currentPath + '/edit/' + item[primary_key]"
                        class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-amber-50 dark:hover:bg-amber-950/60 hover:text-amber-600 transition"
                      >
                        <i class="bi bi-pencil-square text-amber-500"></i>
                        <span>Edit</span>
                      </Link>

                      <button
                        v-if="permissions.can_delete"
                        @click="deleteRow(item[primary_key])"
                        class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition"
                      >
                        <i class="bi bi-trash3 text-rose-500"></i>
                        <span>Delete</span>
                      </button>
                    </div>
                  </div>

                  <!-- Button / Icon Action Style -->
                  <div v-else class="flex items-center justify-end gap-1.5">
                    
                    <!-- Sub-Module Master-Detail Buttons ($this->sub_module[]) -->
                    <template v-for="(sub, sIdx) in sub_module" :key="sIdx">
                      <Link
                        :href="getSubModuleUrl(sub, item)"
                        :title="sub.title"
                        :class="[
                          'inline-flex items-center gap-1.5 px-3 py-1.5 font-bold text-xs rounded-xl shadow-xs transition border',
                          sub.button_color === 'emerald'
                            ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100'
                            : sub.button_color === 'amber'
                            ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800 hover:bg-amber-100'
                            : sub.button_color === 'rose'
                            ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800 hover:bg-rose-100'
                            : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100'
                        ]"
                      >
                        <i :class="[sub.button_icon || 'bi bi-diagram-3-fill', 'text-sm']"></i>
                        <span>{{ sub.title }}</span>
                      </Link>
                    </template>

                    <!-- Custom Add Action Buttons ($this->addaction[]) -->
                    <template v-for="(act, idx) in addaction" :key="idx">
                      <a
                        v-if="shouldShowAddAction(act, item)"
                        :href="getActionUrl(act.url, item)"
                        :target="act.target || '_self'"
                        :title="act.title || act.label"
                        :class="[
                          'inline-flex items-center gap-1.5 font-bold text-xs rounded-lg transition border',
                          permissions.button_action_style === 'button_text' || permissions.button_action_style === 'button_icon_text' ? 'px-3 py-1.5' : 'p-1.5',
                          act.color === 'success' || act.color === 'emerald'
                            ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100'
                            : act.color === 'danger' || act.color === 'rose'
                            ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800 hover:bg-rose-100'
                            : act.color === 'warning' || act.color === 'amber'
                            ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800 hover:bg-amber-100'
                            : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100'
                        ]"
                      >
                        <i v-if="act.icon && permissions.button_action_style !== 'button_text'" :class="[act.icon, 'text-sm']"></i>
                        <span v-if="permissions.button_action_style !== 'button_icon'">{{ act.label }}</span>
                      </a>
                    </template>

                    <!-- Detail Button -->
                    <Link
                      v-if="permissions.can_detail"
                      :href="currentPath + '/detail/' + item[primary_key]"
                      :class="[
                        'inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition',
                        permissions.button_action_style === 'button_text' || permissions.button_action_style === 'button_icon_text' ? 'px-2.5 py-1 text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400' : 'p-1.5'
                      ]"
                      title="View Detail"
                    >
                      <i v-if="permissions.button_action_style !== 'button_text'" class="bi bi-eye text-base"></i>
                      <span v-if="permissions.button_action_style !== 'button_icon'">Detail</span>
                    </Link>

                    <!-- Edit Button -->
                    <Link
                      v-if="permissions.can_edit"
                      :href="currentPath + '/edit/' + item[primary_key]"
                      :class="[
                        'inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition',
                        permissions.button_action_style === 'button_text' || permissions.button_action_style === 'button_icon_text' ? 'px-2.5 py-1 text-xs font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'p-1.5'
                      ]"
                      title="Edit Record"
                    >
                      <i v-if="permissions.button_action_style !== 'button_text'" class="bi bi-pencil-square text-base"></i>
                      <span v-if="permissions.button_action_style !== 'button_icon'">Edit</span>
                    </Link>

                    <!-- Delete Button -->
                    <button
                      v-if="permissions.can_delete"
                      @click="deleteRow(item[primary_key])"
                      :class="[
                        'inline-flex items-center gap-1 rounded-lg text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition',
                        permissions.button_action_style === 'button_text' || permissions.button_action_style === 'button_icon_text' ? 'px-2.5 py-1 text-xs font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' : 'p-1.5'
                      ]"
                      title="Delete Record"
                    >
                      <i v-if="permissions.button_action_style !== 'button_text'" class="bi bi-trash3 text-base"></i>
                      <span v-if="permissions.button_action_style !== 'button_icon'">Delete</span>
                    </button>
                  </div>

                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="!data.data || !data.data.length">
                <td :colspan="columns.length + 2" class="px-4 py-12 text-center">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xl">
                      <i class="bi bi-inbox"></i>
                    </div>
                    <p class="font-bold text-sm text-slate-700 dark:text-slate-300">No records found</p>
                    <p class="text-xs text-slate-400">There are no records matching your current criteria or filter options.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Table Pagination Footer -->
        <div v-if="data.links" class="px-4 py-3.5 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-4 flex-wrap text-xs">
          <div class="text-slate-500 dark:text-slate-400 font-medium">
            Showing <span class="font-bold text-slate-700 dark:text-slate-200">{{ data.from || 0 }}</span> to <span class="font-bold text-slate-700 dark:text-slate-200">{{ data.to || 0 }}</span> of <span class="font-bold text-slate-700 dark:text-slate-200">{{ data.total }}</span> results
          </div>

          <div class="flex items-center gap-1">
            <template v-for="(link, key) in data.links" :key="key">
              <span
                v-if="!link.url"
                class="px-3 py-1.5 rounded-lg text-slate-400 bg-slate-50 dark:bg-slate-800/40 text-[11px]"
                v-html="link.label"
              ></span>
              <Link
                v-else
                :href="link.url"
                :class="[
                  'px-3 py-1.5 rounded-lg font-bold text-[11px] transition',
                  link.active
                    ? 'text-white shadow-xs'
                    : 'text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5'
                ]"
                :style="link.active ? 'background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)))' : ''"
                v-html="link.label"
              ></Link>
            </template>
          </div>
        </div>

      </div>

    </div>

    <!-- CRUDBooster Style Advanced Filter Slide-over Drawer -->
    <Transition name="fade">
      <div v-if="isFilterDrawerOpen" @click="isFilterDrawerOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50"></div>
    </Transition>

    <Transition name="slide-left">
      <div
        v-if="isFilterDrawerOpen"
        class="fixed inset-y-0 right-0 z-50 w-full max-w-lg bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col"
      >
        <!-- Drawer Header -->
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
              <i class="bi bi-funnel-fill text-sm"></i>
            </div>
            <div>
              <h3 class="font-bold text-sm text-slate-900 dark:text-white">Advanced Filter</h3>
              <p class="text-[11px] text-slate-500 dark:text-slate-400">CRUDBooster style column condition filter</p>
            </div>
          </div>

          <button @click="isFilterDrawerOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
            <i class="bi bi-x-lg text-lg"></i>
          </button>
        </div>

        <!-- Drawer Content Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
          <div
            v-for="(row, idx) in filterConditions"
            :key="idx"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-3 relative group"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Condition #{{ idx + 1 }}</span>
              <button @click="removeFilterRow(idx)" class="text-slate-400 hover:text-rose-500 text-xs">
                <i class="bi bi-trash"></i> Remove
              </button>
            </div>

            <!-- Column Select -->
            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Column Name</label>
              <select
                v-model="row.column"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              >
                <option v-for="col in columns" :key="col.name" :value="col.name">
                  {{ col.label }} ({{ col.name }})
                </option>
              </select>
            </div>

            <!-- Operator Select -->
            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Operator</label>
              <select
                v-model="row.type"
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              >
                <option v-for="op in filterOperators" :key="op.value" :value="op.value">
                  {{ op.label }}
                </option>
              </select>
            </div>

            <!-- Filter Value Input -->
            <div v-if="row.type !== 'empty'">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Value</label>
              <input
                v-model="row.value"
                type="text"
                placeholder="Enter search value..."
                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
          </div>

          <button
            @click="addFilterRow"
            class="w-full py-2.5 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2"
          >
            <i class="bi bi-plus-circle"></i>
            <span>Add Filter Condition</span>
          </button>
        </div>

        <!-- Drawer Footer Actions -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between gap-3">
          <button
            @click="resetFilters"
            class="px-4 py-2.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition"
          >
            Reset Filters
          </button>

          <button
            @click="applyFilters"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs border border-indigo-600 transition flex items-center gap-2"
          >
            <i class="bi bi-check-lg"></i>
            <span>Apply Filter</span>
          </button>
        </div>

      </div>
    </Transition>

  </InerminAppLayout>
</template>

<style scoped>
.slide-left-enter-active,
.slide-left-leave-active {
  transition: transform 0.3s ease-in-out;
}
.slide-left-enter-from,
.slide-left-leave-to {
  transform: translateX(100%);
}
</style>
