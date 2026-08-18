<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'
import axios from 'axios'

const props = defineProps({
  page_title: String,
  statistic: Object,
  components: Array,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))

const componentsList = ref(props.components || [])
const activeModalComponent = ref(null)
const isConfigModalOpen = ref(false)
const isSaving = ref(false)
const saveMessage = ref('')

const areas = [
  { id: 'area1', label: 'Area 1 (Top Stat Cards Row)' },
  { id: 'area2', label: 'Area 2 (Main Left Column)' },
  { id: 'area3', label: 'Area 3 (Side Right Column)' },
  { id: 'area4', label: 'Area 4 (Bottom Full Width Panel)' },
]

const availableWidgets = [
  { type: 'smallbox', title: 'Stat Card', icon: 'bi bi-card-heading', desc: 'Single metric stat box with SQL count query' },
  { type: 'chartbar', title: 'Bar Chart', icon: 'bi bi-bar-chart-line', desc: 'Bar chart visualization for grouped data' },
  { type: 'chartline', title: 'Line Chart', icon: 'bi bi-graph-up', desc: 'Line graph visualization for time series' },
  { type: 'panelcustom', title: 'Custom Panel', icon: 'bi bi-code-square', desc: 'Custom HTML or Markdown text block' },
  { type: 'table', title: 'Data Table', icon: 'bi bi-table', desc: 'Tabular database query result table' },
]

const getAreaComponents = (areaId) => {
  return componentsList.value
    .filter(c => (c.area_name || 'area1') === areaId)
    .sort((a, b) => (a.sorting || 0) - (b.sorting || 0))
}

const addWidget = async (type, areaId = 'area1') => {
  isSaving.value = true
  try {
    const res = await axios.post(adminPath.value + '/statistic_builder/add-component', {
      id_cms_statistics: props.statistic.id,
      component_name: type,
      area_name: areaId,
      sorting: getAreaComponents(areaId).length,
    })

    if (res.data.status && res.data.component) {
      componentsList.value.push(res.data.component)
      saveMessage.value = 'Widget added successfully'
    }
  } catch (e) {
    alert('Failed to add widget component')
  } finally {
    isSaving.value = false
    setTimeout(() => saveMessage.value = '', 3000)
  }
}

const openConfigModal = (comp) => {
  activeModalComponent.value = JSON.parse(JSON.stringify(comp))
  if (!activeModalComponent.value.config) {
    activeModalComponent.value.config = {}
  }
  isConfigModalOpen.value = true
}

const saveConfigModal = async () => {
  if (!activeModalComponent.value) return
  isSaving.value = true

  try {
    await axios.post(adminPath.value + '/statistic_builder/save-component', {
      componentID: activeModalComponent.value.componentID,
      config: activeModalComponent.value.config,
    })

    const idx = componentsList.value.findIndex(c => c.componentID === activeModalComponent.value.componentID)
    if (idx !== -1) {
      componentsList.value[idx].config = { ...activeModalComponent.value.config }
    }

    isConfigModalOpen.value = false
    saveMessage.value = 'Widget settings saved'
  } catch (e) {
    alert('Failed to save widget configuration')
  } finally {
    isSaving.value = false
    setTimeout(() => saveMessage.value = '', 3000)
  }
}

const removeWidget = async (componentID) => {
  if (!confirm('Are you sure you want to remove this widget?')) return
  isSaving.value = true

  try {
    await axios.post(adminPath.value + '/statistic_builder/delete-component/' + componentID)
    componentsList.value = componentsList.value.filter(c => c.componentID !== componentID)
    saveMessage.value = 'Widget removed'
  } catch (e) {
    alert('Failed to delete widget')
  } finally {
    isSaving.value = false
    setTimeout(() => saveMessage.value = '', 3000)
  }
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans w-full">
      
      <!-- Top Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-stone-400">
            <Link :href="adminPath + '/statistic_builder'" class="hover:text-stone-200">Statistic Builder</Link>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-[rgb(var(--accent-rgb))] font-bold">{{ statistic.name }}</span>
          </div>
          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 dark:text-white mt-1">
            Visual Dashboard Builder
          </h1>
          <p class="text-xs text-stone-400 font-medium mt-0.5">Drag, place, and configure real-time statistic components for <span class="font-bold text-stone-300">/{{ statistic.slug }}</span></p>
        </div>

        <div class="flex items-center gap-3">
          <span v-if="saveMessage" class="text-xs font-bold text-emerald-500 animate-pulse flex items-center gap-1">
            <i class="bi bi-check-circle-fill"></i> {{ saveMessage }}
          </span>

          <Link
            :href="adminPath + '/statistic_builder/show/' + statistic.slug"
            target="_blank"
            class="px-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] text-xs font-bold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-white/5 transition flex items-center gap-2"
          >
            <i class="bi bi-eye"></i>
            <span>Preview Dashboard</span>
          </Link>

          <Link
            :href="adminPath + '/statistic_builder'"
            class="px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow-md transition flex items-center gap-2"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
          >
            <i class="bi bi-check-lg"></i>
            <span>Done</span>
          </Link>
        </div>
      </div>

      <!-- Builder Layout Shell Grid (Left Canvas & Right Palette) -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- CANVAS (Left 3 Columns) -->
        <div class="lg:col-span-3 space-y-6">
          
          <div v-for="area in areas" :key="area.id" class="card rounded-2xl p-5 shadow-sm border border-stone-200 dark:border-white/5 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-200 dark:border-white/5 pb-3">
              <span class="text-xs font-bold uppercase tracking-wider text-stone-400 flex items-center gap-2">
                <i class="bi bi-grid-fill text-[rgb(var(--accent-rgb))]"></i>
                {{ area.label }}
              </span>

              <div class="flex items-center gap-1.5">
                <button
                  v-for="w in availableWidgets"
                  :key="w.type"
                  @click="addWidget(w.type, area.id)"
                  class="px-2.5 py-1 rounded-lg text-[10px] font-bold border border-stone-200 dark:border-white/10 hover:bg-stone-100 dark:hover:bg-white/5 text-stone-700 dark:text-stone-300 transition flex items-center gap-1"
                >
                  <i :class="w.icon"></i>
                  <span>+ {{ w.title }}</span>
                </button>
              </div>
            </div>

            <!-- Components Container inside Area -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 min-h-[90px] p-2 rounded-xl bg-stone-50/50 dark:bg-white/[0.01] border border-dashed border-stone-200 dark:border-white/10">
              
              <div
                v-for="comp in getAreaComponents(area.id)"
                :key="comp.componentID"
                class="card rounded-xl p-4 shadow-sm relative group hover:border-[rgb(var(--accent-rgb))] transition flex flex-col justify-between"
              >
                <!-- Top Header & Action Buttons -->
                <div class="flex items-center justify-between border-b border-stone-100 dark:border-white/5 pb-2.5 mb-2.5">
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background: rgb(var(--accent-rgb));"></span>
                    <span class="text-xs font-bold text-stone-900 dark:text-white truncate">
                      {{ comp.config?.name || 'Untitled Widget' }}
                    </span>
                  </div>

                  <div class="flex items-center gap-1">
                    <button
                      @click="openConfigModal(comp)"
                      class="p-1.5 rounded-lg text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-white/5 transition"
                      title="Configure Widget"
                    >
                      <i class="bi bi-gear-fill text-xs"></i>
                    </button>
                    
                    <button
                      @click="removeWidget(comp.componentID)"
                      class="p-1.5 rounded-lg text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                      title="Delete Widget"
                    >
                      <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                  </div>
                </div>

                <!-- Preview Box Representation -->
                <div class="py-3 px-2 text-center bg-stone-100/50 dark:bg-white/5 rounded-lg border border-stone-200/50 dark:border-white/5">
                  <i :class="[comp.config?.icon || 'bi bi-boxes', 'text-2xl text-[rgb(var(--accent-rgb))]']"></i>
                  <p class="text-[11px] font-bold text-stone-700 dark:text-stone-300 mt-1 uppercase">{{ comp.component_name }}</p>
                  <p class="text-[10px] font-mono text-stone-400 truncate mt-0.5">{{ comp.config?.sql || 'SQL query ready' }}</p>
                </div>
              </div>

              <!-- Empty Area Drop Placeholder -->
              <div v-if="!getAreaComponents(area.id).length" class="col-span-full py-8 text-center text-stone-400 text-xs font-medium">
                <i class="bi bi-plus-circle text-xl text-stone-300 dark:text-stone-600 block mb-1"></i>
                <span>No widgets in {{ area.id }}. Click the buttons above to add widgets!</span>
              </div>

            </div>
          </div>

        </div>

        <!-- RIGHT PALETTE (1 Column) -->
        <div>
          <div class="card rounded-2xl p-5 shadow-sm space-y-4 sticky top-24">
            <h3 class="font-display text-sm font-bold text-stone-900 dark:text-white uppercase tracking-wider">
              Available Widgets
            </h3>
            <p class="text-xs text-stone-400 font-medium">Click any widget to add it to your dashboard layout</p>

            <div class="space-y-3">
              <div
                v-for="w in availableWidgets"
                :key="w.type"
                @click="addWidget(w.type, 'area1')"
                class="p-3.5 rounded-xl border border-stone-200 dark:border-white/10 hover:border-[rgb(var(--accent-rgb))] bg-stone-50/50 dark:bg-white/[0.02] hover:bg-stone-100 dark:hover:bg-white/5 cursor-pointer transition group flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition" style="background: rgba(var(--accent-rgb), 0.15); color: rgb(var(--accent-rgb));">
                  <i :class="w.icon"></i>
                </div>
                <div>
                  <h4 class="text-xs font-bold text-stone-900 dark:text-white group-hover:text-[rgb(var(--accent-rgb))] transition">{{ w.title }}</h4>
                  <p class="text-[10px] text-stone-400 leading-tight">{{ w.desc }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Widget Configuration Modal -->
      <div v-if="isConfigModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="card w-full max-w-lg rounded-2xl p-6 shadow-2xl space-y-5 border border-stone-200 dark:border-white/10">
          
          <div class="flex items-center justify-between border-b border-stone-200 dark:border-white/5 pb-3">
            <h3 class="font-display text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
              <i class="bi bi-sliders text-[rgb(var(--accent-rgb))]"></i>
              Configure {{ activeModalComponent?.component_name }} Widget
            </h3>
            <button @click="isConfigModalOpen = false" class="text-stone-400 hover:text-white">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="space-y-4 text-xs font-semibold">
            <!-- Widget Name -->
            <div>
              <label class="block text-stone-400 mb-1">Widget Title / Name</label>
              <input
                v-model="activeModalComponent.config.name"
                type="text"
                class="w-full px-3.5 py-2.5 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl text-stone-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))]"
                placeholder="e.g. Total Revenue"
              />
            </div>

            <!-- Icon -->
            <div>
              <label class="block text-stone-400 mb-1">Bootstrap Icon Class</label>
              <input
                v-model="activeModalComponent.config.icon"
                type="text"
                class="w-full px-3.5 py-2.5 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl text-stone-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))]"
                placeholder="e.g. bi bi-people"
              />
            </div>

            <!-- SQL Query -->
            <div>
              <label class="block text-stone-400 mb-1">SQL Query (Real-time DB Query)</label>
              <textarea
                v-model="activeModalComponent.config.sql"
                rows="3"
                class="w-full px-3.5 py-2.5 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl text-stone-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))]"
                placeholder="SELECT COUNT(*) FROM cms_users"
              ></textarea>
              <p class="text-[10px] text-stone-400 font-normal mt-1">Make sure query returns valid column count or dataset.</p>
            </div>

            <!-- Link URL -->
            <div>
              <label class="block text-stone-400 mb-1">Target Link URL (Optional)</label>
              <input
                v-model="activeModalComponent.config.link"
                type="text"
                class="w-full px-3.5 py-2.5 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl text-stone-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))]"
                placeholder="/administrator/users"
              />
            </div>

            <!-- Custom HTML (if panelcustom) -->
            <div v-if="activeModalComponent?.component_name === 'panelcustom'">
              <label class="block text-stone-400 mb-1">Custom HTML Content</label>
              <textarea
                v-model="activeModalComponent.config.html"
                rows="4"
                class="w-full px-3.5 py-2.5 bg-stone-100 dark:bg-white/5 border border-stone-200 dark:border-white/10 rounded-xl text-stone-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[rgb(var(--accent-rgb))]"
                placeholder="<p>Custom HTML panel content</p>"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 border-t border-stone-200 dark:border-white/5 pt-4">
            <button
              @click="isConfigModalOpen = false"
              class="px-4 py-2 rounded-xl text-xs font-bold text-stone-400 hover:text-stone-200"
            >
              Cancel
            </button>
            <button
              @click="saveConfigModal"
              class="px-5 py-2 rounded-xl text-xs font-bold text-white shadow-md transition"
              style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
            >
              Save Configuration
            </button>
          </div>

        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>
