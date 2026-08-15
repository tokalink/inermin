<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  title: String,
  table: String,
  valueColumn: {
    type: String,
    default: 'id',
  },
  labelColumn: {
    type: String,
    default: 'name',
  },
  columns: {
    type: String,
    default: '',
  },
  where: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'select'])

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))

import { computed } from 'vue'

const searchQuery = ref('')
const loading = ref(false)
const items = ref([])
const availableColumns = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const totalItems = ref(0)

const fetchLovData = async (pageNo = 1) => {
  if (!props.table) return
  loading.value = true
  currentPage.value = pageNo

  try {
    const res = await axios.get(adminPath.value + '/lov-data', {
      params: {
        table: props.table,
        column_value: props.valueColumn,
        column_label: props.labelColumn,
        columns: props.columns || props.labelColumn,
        where: props.where,
        q: searchQuery.value,
        page: pageNo,
        limit: 8,
      },
    })

    items.value = res.data.data || []
    currentPage.value = res.data.current_page || 1
    lastPage.value = res.data.last_page || 1
    totalItems.value = res.data.total || 0
    availableColumns.value = res.data.columns || []
  } catch (err) {
    console.error('Failed to fetch LOV data:', err)
  } finally {
    loading.value = false
  }
}

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      searchQuery.value = ''
      fetchLovData(1)
    }
  }
)

const handleSearch = () => {
  fetchLovData(1)
}

const selectRow = (row) => {
  emit('select', row)
  emit('close')
}

const formatColumnName = (col) => {
  return ucwords(col.replace(/_/g, ' '))
}

const ucwords = (str) => {
  return str.replace(/\b\w/g, (l) => l.toUpperCase())
}
</script>

<template>
  <Transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="emit('close')"
    >
      <div
        class="card rounded-3xl w-full max-w-3xl shadow-2xl overflow-hidden border border-stone-200 dark:border-white/10 flex flex-col max-h-[90vh] bg-white dark:bg-[#15130f]"
      >
        <!-- Modal Header -->
        <div class="p-5 border-b border-stone-200 dark:border-white/5 flex items-center justify-between bg-stone-50/50 dark:bg-white/[0.02]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white text-base shadow-md" style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));">
              <i class="bi bi-list-nested"></i>
            </div>
            <div>
              <h3 class="font-display font-bold text-sm text-stone-900 dark:text-white">
                {{ title || 'List of Values (LOV)' }}
              </h3>
              <p class="text-[11px] text-stone-400 font-medium">
                Lookup Table: <span class="font-mono text-[rgb(var(--accent-rgb))] font-bold">{{ table }}</span>
              </p>
            </div>
          </div>

          <button
            @click="emit('close')"
            class="p-2 rounded-xl text-stone-400 hover:text-stone-700 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-white/5 transition"
          >
            <i class="bi bi-x-lg text-sm"></i>
          </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 border-b border-stone-200/60 dark:border-white/5 bg-stone-100/40 dark:bg-white/[0.01]">
          <form @submit.prevent="handleSearch" class="flex items-center gap-2">
            <div class="relative flex-1">
              <i class="bi bi-search absolute left-3.5 top-2.5 text-stone-400 text-sm"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search values by keywords..."
                class="w-full pl-10 pr-4 py-2 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
              />
            </div>
            <button
              type="submit"
              class="px-4 py-2 rounded-xl text-white text-xs font-bold shadow-md transition flex items-center gap-1.5"
              style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
            >
              <span>Search</span>
            </button>
          </form>
        </div>

        <!-- Data Table Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
          <div v-if="loading" class="p-12 text-center space-y-3">
            <i class="bi bi-arrow-repeat animate-spin text-2xl text-[rgb(var(--accent-rgb))]"></i>
            <p class="text-xs font-medium text-stone-400">Fetching List of Values...</p>
          </div>

          <template v-else-if="items.length">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-stone-200 dark:border-white/5 text-[10px] uppercase font-bold text-stone-400 bg-stone-50/50 dark:bg-white/[0.02]">
                    <th v-for="col in availableColumns" :key="col" class="px-4 py-3">
                      {{ formatColumnName(col) }}
                    </th>
                    <th class="px-4 py-3 text-right">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-white/5">
                  <tr
                    v-for="(row, idx) in items"
                    :key="idx"
                    @click="selectRow(row)"
                    class="hover:bg-amber-500/[0.04] dark:hover:bg-[rgba(var(--accent-rgb),0.06)] transition cursor-pointer group"
                  >
                    <td v-for="col in availableColumns" :key="col" class="px-4 py-3 text-stone-800 dark:text-stone-200 font-medium">
                      <span v-if="col === valueColumn" class="font-mono text-[11px] px-2 py-0.5 rounded bg-stone-100 dark:bg-white/10 font-bold text-stone-700 dark:text-stone-300">
                        {{ row[col] }}
                      </span>
                      <span v-else>
                        {{ row[col] }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button
                        type="button"
                        @click.stop="selectRow(row)"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[rgb(var(--accent-rgb))]/10 text-[rgb(var(--accent-rgb))] hover:bg-[rgb(var(--accent-rgb))] hover:text-white transition inline-flex items-center gap-1"
                      >
                        <i class="bi bi-check2"></i>
                        <span>Select</span>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <div v-else class="p-12 text-center space-y-2">
            <i class="bi bi-inbox text-3xl text-stone-400"></i>
            <p class="text-xs font-bold text-stone-700 dark:text-stone-300">No matching values found</p>
            <p class="text-[11px] text-stone-400">Try adjusting your search criteria</p>
          </div>
        </div>

        <!-- Modal Footer & Pagination -->
        <div class="p-4 border-t border-stone-200 dark:border-white/5 bg-stone-50/50 dark:bg-white/[0.02] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <div class="text-stone-400 font-medium text-[11px]">
            Showing page <span class="font-bold text-stone-800 dark:text-stone-200">{{ currentPage }}</span> of <span class="font-bold text-stone-800 dark:text-stone-200">{{ lastPage }}</span> ({{ totalItems }} total records)
          </div>

          <div class="flex items-center gap-1.5">
            <button
              @click="fetchLovData(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="px-3 py-1.5 rounded-xl border border-stone-200 dark:border-white/10 text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 font-bold transition disabled:opacity-40"
            >
              Prev
            </button>
            <button
              @click="fetchLovData(currentPage + 1)"
              :disabled="currentPage >= lastPage"
              class="px-3 py-1.5 rounded-xl border border-stone-200 dark:border-white/10 text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 font-bold transition disabled:opacity-40"
            >
              Next
            </button>
          </div>
        </div>

      </div>
    </div>
  </Transition>
</template>
