<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  statistic: Object,
  components: Array,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))

const getAreaComponents = (areaId) => {
  return (props.components || [])
    .filter(c => (c.area_name || 'area1') === areaId)
    .sort((a, b) => (a.sorting || 0) - (b.sorting || 0))
}

const formatStatValue = (val) => {
  if (val === null || val === undefined) return '0'
  if (typeof val === 'number' || typeof val === 'string') return val
  if (Array.isArray(val) && val.length > 0) {
    const first = val[0]
    if (typeof first === 'object' && first !== null) {
      return Object.values(first)[0] ?? '0'
    }
    return first
  }
  if (typeof val === 'object' && val !== null) {
    return Object.values(val)[0] ?? '0'
  }
  return String(val)
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 dark:text-white">
            {{ statistic.name }}
          </h1>
          <p class="text-xs text-stone-400 font-medium mt-1">Real-time custom statistic dashboard panel</p>
        </div>

        <Link
          :href="adminPath + '/statistic_builder/builder/' + statistic.id"
          class="px-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] text-xs font-bold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-white/5 transition flex items-center gap-2 self-start sm:self-auto"
        >
          <i class="bi bi-pencil-square text-xs"></i>
          <span>Edit in Builder</span>
        </Link>
      </div>

      <!-- AREA 1: STAT CARDS ROW -->
      <div v-if="getAreaComponents('area1').length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="comp in getAreaComponents('area1')"
          :key="comp.componentID"
          class="card stat-card p-5 rounded-2xl shadow-sm flex items-center justify-between"
        >
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-widest">{{ comp.config?.name || 'Metric' }}</p>
            <h3 class="font-display text-3xl font-extrabold text-stone-900 dark:text-white mt-1">{{ formatStatValue(comp.value) }}</h3>
            
            <a v-if="comp.config?.link" :href="comp.config.link" class="text-[11px] font-bold text-[rgb(var(--accent-rgb))] hover:underline flex items-center gap-1 mt-1">
              <span>View details</span>
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0" style="background: rgba(var(--accent-rgb), 0.14); color: rgb(var(--accent-rgb));">
            <i :class="comp.config?.icon || 'bi bi-boxes'"></i>
          </div>
        </div>
      </div>

      <!-- AREA 2 & AREA 3 GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- AREA 2: MAIN COLUMN (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
          <div v-for="comp in getAreaComponents('area2')" :key="comp.componentID" class="card rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-stone-200 dark:border-white/5 pb-3">
              <h3 class="font-display text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i :class="[comp.config?.icon || 'bi bi-bar-chart', 'text-[rgb(var(--accent-rgb))]']"></i>
                {{ comp.config?.name || 'Chart Widget' }}
              </h3>
            </div>

            <!-- Visual Table or Representation -->
            <div v-if="Array.isArray(comp.value) && comp.value.length" class="overflow-x-auto">
              <table class="w-full text-left text-xs font-medium border-collapse">
                <thead>
                  <tr class="border-b border-stone-200 dark:border-white/5 text-[10px] text-stone-400 uppercase font-semibold">
                    <th v-for="(val, key) in comp.value[0]" :key="key" class="py-2.5 px-3">{{ key }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-white/5">
                  <tr v-for="(row, idx) in comp.value" :key="idx" class="hover:bg-stone-50 dark:hover:bg-white/[0.02]">
                    <td v-for="(val, k) in row" :key="k" class="py-2.5 px-3 text-stone-700 dark:text-stone-300">{{ val }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-else-if="comp.component_name === 'panelcustom'" v-html="comp.config?.html || comp.value" class="text-xs text-stone-700 dark:text-stone-300"></div>

            <div v-else class="text-center py-6 text-xs text-stone-400 font-bold">
              <span>Result: {{ formatStatValue(comp.value) }}</span>
            </div>
          </div>
        </div>

        <!-- AREA 3: SIDEBAR COLUMN (1 Col) -->
        <div class="space-y-6">
          <div v-for="comp in getAreaComponents('area3')" :key="comp.componentID" class="card rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-display text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
              <i :class="[comp.config?.icon || 'bi bi-info-circle', 'text-[rgb(var(--accent-rgb))]']"></i>
              {{ comp.config?.name || 'Widget' }}
            </h3>

            <div v-if="comp.component_name === 'panelcustom'" v-html="comp.config?.html || comp.value" class="text-xs text-stone-700 dark:text-stone-300"></div>
            
            <div v-else class="text-stone-900 dark:text-white font-display text-2xl font-extrabold">
              {{ formatStatValue(comp.value) }}
            </div>
          </div>
        </div>

      </div>

      <!-- AREA 4: BOTTOM FULL WIDTH PANEL -->
      <div v-if="getAreaComponents('area4').length" class="space-y-6">
        <div v-for="comp in getAreaComponents('area4')" :key="comp.componentID" class="card rounded-2xl p-6 shadow-sm space-y-4 border border-stone-200 dark:border-white/5">
          <h3 class="font-display text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
            <i :class="[comp.config?.icon || 'bi bi-grid-3x3', 'text-[rgb(var(--accent-rgb))]']"></i>
            {{ comp.config?.name || 'Full Width Table / Widget' }}
          </h3>

          <div v-if="Array.isArray(comp.value) && comp.value.length" class="overflow-x-auto">
            <table class="w-full text-left text-xs font-medium border-collapse">
              <thead>
                <tr class="border-b border-stone-200 dark:border-white/5 text-[10px] text-stone-400 uppercase font-semibold">
                  <th v-for="(val, key) in comp.value[0]" :key="key" class="py-2.5 px-3">{{ key }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100 dark:divide-white/5">
                <tr v-for="(row, idx) in comp.value" :key="idx" class="hover:bg-stone-50 dark:hover:bg-white/[0.02]">
                  <td v-for="(val, k) in row" :key="k" class="py-2.5 px-3 text-stone-700 dark:text-stone-300">{{ val }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else-if="comp.component_name === 'panelcustom'" v-html="comp.config?.html || comp.value" class="text-xs text-stone-700 dark:text-stone-300"></div>

          <div v-else class="text-stone-400 text-xs">
            <span>Result: {{ formatStatValue(comp.value) }}</span>
          </div>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
