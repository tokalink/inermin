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
  button_selected: Array,
  index_statistic: Array,
  alerts: Array,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

const searchQuery = ref(props.filters?.q || '')
const currentOrderby = ref(props.filters?.orderby || '')
const currentLimit = ref(props.filters?.limit || 20)

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
  router.get(currentPath.value, {
    q: searchQuery.value,
    orderby: currentOrderby.value,
    limit: currentLimit.value
  }, { preserveState: true })
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
  if (confirm('Are you sure you want to delete this row?')) {
    router.get(currentPath.value + '/delete/' + id)
  }
}

const isImage = (val) => {
  if (typeof val !== 'string') return false
  return val.match(/\.(jpeg|jpg|gif|png|webp|svg)/i) != null || val.startsWith('storage/') || val.startsWith('http')
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans">
      
      <!-- Page Header & Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage and view data for {{ table_name }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Link
            v-if="permissions.can_add"
            :href="currentPath + '/add'"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition duration-150"
          >
            <i class="bi bi-plus-lg text-sm"></i>
            <span>Add Data</span>
          </Link>

          <a
            v-if="permissions.can_export"
            :href="currentPath + '/export-data'"
            target="_blank"
            class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 font-semibold text-xs rounded-xl shadow-sm transition"
          >
            <i class="bi bi-download text-sm text-emerald-500"></i>
            <span>Export</span>
          </a>
        </div>
      </div>

      <!-- Alert Header Messages -->
      <div v-for="(alertItem, index) in alerts" :key="index" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-semibold">
        {{ alertItem.message || alertItem }}
      </div>

      <!-- Main Table Container Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Toolbar (Search & Filters & Bulk Actions) -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
          
          <!-- Bulk Action Options -->
          <div v-if="permissions.can_bulk_action && selectedRows.length" class="flex items-center gap-2">
            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800">
              {{ selectedRows.length }} Selected
            </span>
            
            <button
              @click="executeBulkAction('delete')"
              class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5"
            >
              <i class="bi bi-trash"></i> Delete Selected
            </button>
          </div>
          <div v-else class="flex items-center gap-2 text-xs text-slate-500">
            <span>Show</span>
            <select
              v-model="currentLimit"
              @change="handleSearch"
              class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <span>entries</span>
          </div>

          <!-- Global Search Input -->
          <div class="relative w-full md:w-72">
            <input
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Search & hit Enter..."
              class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
            />
            <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
          </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/40 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                
                <th v-if="permissions.can_bulk_action" class="p-3 w-10 text-center">
                  <input
                    type="checkbox"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>

                <th
                  v-for="col in columns"
                  :key="col.name"
                  @click="handleSort(col.name)"
                  class="p-3 cursor-pointer hover:text-slate-700 dark:hover:text-slate-200 select-none transition"
                >
                  <div class="flex items-center gap-1.5">
                    <span>{{ col.label }}</span>
                    <i class="bi bi-arrow-down-up text-[10px] opacity-40"></i>
                  </div>
                </th>

                <th class="p-3 text-right">Actions</th>
              </tr>
            </thead>
            
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
              <tr
                v-for="row in data.data"
                :key="row[primary_key]"
                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors"
              >
                <td v-if="permissions.can_bulk_action" class="p-3 text-center">
                  <input
                    type="checkbox"
                    :value="row[primary_key]"
                    v-model="selectedRows"
                    class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>

                <td v-for="col in columns" :key="col.name" class="p-3 text-slate-700 dark:text-slate-300 font-medium">
                  
                  <!-- Image Column Rendering -->
                  <div v-if="col.image || isImage(row[col.name])" class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                    <img v-if="row[col.name]" :src="row[col.name].startsWith('http') ? row[col.name] : '/' + row[col.name]" class="w-full h-full object-cover" alt="Image" />
                    <i v-else class="bi bi-image text-slate-400"></i>
                  </div>

                  <!-- Standard Text Cell -->
                  <span v-else class="truncate max-w-xs block">
                    {{ row[col.name] !== null && row[col.name] !== undefined ? row[col.name] : '-' }}
                  </span>

                </td>

                <!-- Action Buttons Column -->
                <td class="p-3 text-right">
                  <div class="inline-flex items-center gap-1">
                    
                    <Link
                      v-if="permissions.can_detail"
                      :href="currentPath + '/detail/' + row[primary_key]"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition"
                      title="Detail"
                    >
                      <i class="bi bi-eye text-sm"></i>
                    </Link>

                    <Link
                      v-if="permissions.can_edit"
                      :href="currentPath + '/edit/' + row[primary_key]"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition"
                      title="Edit"
                    >
                      <i class="bi bi-pencil text-sm"></i>
                    </Link>

                    <button
                      v-if="permissions.can_delete"
                      @click="deleteRow(row[primary_key])"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                      title="Delete"
                    >
                      <i class="bi bi-trash text-sm"></i>
                    </button>

                  </div>
                </td>
              </tr>

              <tr v-if="!data.data.length">
                <td :colspan="columns.length + 2" class="p-8 text-center text-slate-400 dark:text-slate-500">
                  <i class="bi bi-inbox text-3xl block mb-2 opacity-50"></i>
                  No data available
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Footer -->
        <div v-if="data.links" class="p-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50 text-xs text-slate-500 dark:text-slate-400">
          <div>
            Showing <span class="font-bold text-slate-800 dark:text-slate-200">{{ data.from || 0 }}</span> to <span class="font-bold text-slate-800 dark:text-slate-200">{{ data.to || 0 }}</span> of <span class="font-bold text-slate-800 dark:text-slate-200">{{ data.total }}</span> entries
          </div>

          <div class="flex items-center gap-1 flex-wrap">
            <template v-for="(link, idx) in data.links" :key="idx">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1.5 rounded-lg font-medium transition',
                  link.active
                    ? 'bg-indigo-600 text-white shadow-sm'
                    : 'hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                ]"
              ></Link>
              <span v-else v-html="link.label" class="px-3 py-1.5 text-slate-400 opacity-50"></span>
            </template>
          </div>
        </div>

      </div>
    </div>
  </InerminAppLayout>
</template>
