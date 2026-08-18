<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import InerminAppLayout from './InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  groups: Array,
  settings: Object,
})

const page = usePage()
const currentPath = computed(() => page.url.split('?')[0])

// Active Tab Group State
const activeGroup = ref(props.groups && props.groups.length ? props.groups[0] : 'General Setting')

// Form values & file inputs state
const formData = ref({})
const filePreviews = ref({})
const isSubmitting = ref(false)

// Initialize form values from props.settings
if (props.settings) {
  Object.keys(props.settings).forEach(group => {
    props.settings[group].forEach(setting => {
      formData.value[setting.name] = setting.content || ''
    })
  })
}

// Handle Image/File Input Change for live preview
const handleFileChange = (e, settingName) => {
  const file = e.target.files[0]
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (event) => {
      filePreviews.value[settingName] = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Add New Setting Modal State
const isAddModalOpen = ref(false)
const newSetting = ref({
  name: '',
  label: '',
  group_setting: activeGroup.value,
  content_input_type: 'text',
  dataenum: '',
  helper: '',
  content: '',
})

const submitAddSetting = () => {
  router.post(currentPath.value + '/add', newSetting.value, {
    onSuccess: () => {
      isAddModalOpen.value = false
      newSetting.value = {
        name: '',
        label: '',
        group_setting: activeGroup.value,
        content_input_type: 'text',
        dataenum: '',
        helper: '',
        content: '',
      }
    }
  })
}

const deleteSettingKey = (id, name) => {
  if (confirm(`Are you sure you want to delete setting key "${name}"?`)) {
    router.post(currentPath.value + '/delete-setting/' + id)
  }
}

const parseEnumOptions = (dataenum) => {
  if (!dataenum) return []
  let items = []
  if (typeof dataenum === 'string') {
    items = dataenum.split(',').map(item => item.trim())
  } else {
    items = dataenum
  }
  return items.map(item => {
    if (typeof item === 'string' && item.includes(':')) {
      const parts = item.split(':')
      return { value: parts[0].trim(), label: parts.slice(1).join(':').trim() }
    }
    return { value: item, label: item }
  })
}
</script>

<template>
  <InerminAppLayout>
    <div class="space-y-6 font-sans max-w-5xl mx-auto">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ page_title }}</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Manage application configuration parameters, branding, and system preferences</p>
        </div>

        <button
          @click="isAddModalOpen = true"
          class="px-4 py-2.5 rounded-xl text-xs font-bold text-white flex items-center gap-2 shadow-md transition-transform hover:scale-105 active:scale-95"
          style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Add Setting Key</span>
        </button>
      </div>

      <!-- Tabbed Group Navigation Bar -->
      <div class="flex items-center gap-2 border-b border-stone-200 dark:border-stone-800 overflow-x-auto pb-1 custom-scrollbar">
        <button
          v-for="group in groups"
          :key="group"
          @click="activeGroup = group"
          :class="[
            'px-5 py-2.5 font-bold text-xs rounded-t-xl transition whitespace-nowrap flex items-center gap-2 border-b-2',
            activeGroup === group
              ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30'
              : 'border-transparent text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-stone-200'
          ]"
        >
          <i class="bi bi-gear-wide-connected text-sm"></i>
          <span>{{ group }}</span>
          <span class="px-1.5 py-0.5 text-[10px] rounded-full bg-stone-200 dark:bg-stone-800 font-extrabold text-stone-700 dark:text-stone-300">
            {{ settings[group] ? settings[group].length : 0 }}
          </span>
        </button>
      </div>

      <!-- Settings Form Body Card -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8">
        
        <form :action="currentPath + '/save'" method="POST" enctype="multipart/form-data" class="space-y-6">
          <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />

          <!-- Group Header -->
          <div class="pb-4 border-b border-stone-100 dark:border-white/5 flex items-center justify-between">
            <div>
              <h2 class="text-base font-bold text-stone-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-sliders text-indigo-500"></i>
                <span>{{ activeGroup }}</span>
              </h2>
              <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">Configure setting values for group <span class="font-bold text-stone-700 dark:text-stone-300">{{ activeGroup }}</span></p>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="!settings[activeGroup] || !settings[activeGroup].length" class="py-12 text-center text-stone-400 text-xs">
            <i class="bi bi-inbox text-3xl block mb-2 opacity-50"></i>
            No settings found in group "{{ activeGroup }}". Click "Add Setting Key" above to create one.
          </div>

          <!-- Settings Items List -->
          <div v-else class="space-y-6">
            <div
              v-for="stg in settings[activeGroup]"
              :key="stg.id"
              class="p-5 rounded-2xl bg-stone-50/70 dark:bg-white/[0.02] border border-stone-200/60 dark:border-white/5 space-y-2 relative group"
            >
              
              <!-- Setting Header Label & Key Badge -->
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-stone-800 dark:text-stone-200 flex items-center gap-2">
                  <span>{{ stg.label || stg.name }}</span>
                  <span class="font-mono text-[10px] bg-stone-200 dark:bg-stone-800 text-stone-600 dark:text-stone-400 px-2 py-0.5 rounded-md font-semibold">
                    ${{ stg.name }}
                  </span>
                </label>

                <!-- Delete Key Action -->
                <button
                  type="button"
                  @click="deleteSettingKey(stg.id, stg.name)"
                  class="text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 text-xs transition"
                  title="Delete Setting Key"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>

              <!-- Input Fields rendering according to content_input_type -->
              <div>
                
                <!-- Textarea -->
                <template v-if="stg.content_input_type === 'textarea'">
                  <textarea
                    :name="stg.name"
                    v-model="formData[stg.name]"
                    rows="3"
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                  ></textarea>
                </template>

                <!-- Upload Image / Upload File -->
                <template v-else-if="stg.content_input_type === 'upload_image' || stg.content_input_type === 'upload_file' || stg.content_input_type === 'image' || stg.content_input_type === 'file'">
                  <div class="space-y-3">
                    
                    <!-- Preview existing image or newly uploaded preview -->
                    <div v-if="filePreviews[stg.name] || stg.content" class="flex items-center gap-4">
                      <div class="w-16 h-16 rounded-2xl overflow-hidden border border-stone-200 dark:border-stone-700 shadow-sm bg-stone-100 dark:bg-stone-800 flex items-center justify-center">
                        <img :src="filePreviews[stg.name] || stg.content" class="w-full h-full object-cover" alt="Preview" />
                      </div>
                      <div>
                        <p class="text-[11px] font-bold text-stone-700 dark:text-stone-300">Current File / Image</p>
                        <a :href="stg.content" target="_blank" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-mono truncate max-w-xs block">
                          {{ stg.content }}
                        </a>
                      </div>
                    </div>

                    <!-- File input -->
                    <input
                      type="file"
                      :name="stg.name"
                      @change="(e) => handleFileChange(e, stg.name)"
                      class="block w-full text-xs text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/50 dark:file:text-indigo-400 cursor-pointer"
                    />
                  </div>
                </template>

                <!-- Select Dropdown -->
                <template v-else-if="stg.content_input_type === 'select'">
                  <select
                    :name="stg.name"
                    v-model="formData[stg.name]"
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                  >
                    <option v-for="opt in parseEnumOptions(stg.dataenum)" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </template>

                <!-- Regular Input (text, email, number, password, etc) -->
                <template v-else>
                  <input
                    :type="stg.content_input_type || 'text'"
                    :name="stg.name"
                    v-model="formData[stg.name]"
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                  />
                </template>

              </div>

              <!-- Helper Description Text -->
              <p v-if="stg.helper" class="text-[11px] text-stone-500 dark:text-stone-400 font-medium">
                <i class="bi bi-info-circle text-xs mr-1 text-indigo-500"></i>
                {{ stg.helper }}
              </p>

            </div>
          </div>

          <!-- Form Submit Footer -->
          <div v-if="settings[activeGroup] && settings[activeGroup].length" class="pt-4 border-t border-stone-100 dark:border-white/5 flex justify-end">
            <button
              type="submit"
              class="px-6 py-2.5 rounded-xl text-xs font-bold text-white shadow-lg transition-transform hover:scale-105 active:scale-95 flex items-center gap-2"
              style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep))); box-shadow: 0 6px 20px -6px rgba(var(--accent-rgb), 0.5);"
            >
              <i class="bi bi-check-lg text-sm"></i>
              <span>Save Settings</span>
            </button>
          </div>

        </form>

      </div>

    </div>

    <!-- Add New Setting Key Modal -->
    <Transition name="fade">
      <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="card w-full max-w-md rounded-2xl p-6 shadow-2xl space-y-4 border border-stone-200 dark:border-white/10">
          
          <div class="flex items-center justify-between border-b border-stone-100 dark:border-white/5 pb-3">
            <h3 class="font-bold text-base text-stone-900 dark:text-white flex items-center gap-2">
              <i class="bi bi-plus-circle text-indigo-500"></i>
              <span>Add Setting Key</span>
            </h3>
            <button @click="isAddModalOpen = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-white">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <form @submit.prevent="submitAddSetting" class="space-y-4">
            
            <div class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Setting Key Name (variable name)</label>
              <input
                v-model="newSetting.name"
                type="text"
                required
                placeholder="e.g. app_logo, company_phone"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Label Title</label>
              <input
                v-model="newSetting.label"
                type="text"
                required
                placeholder="e.g. Application Logo, Company Phone"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Group Setting</label>
              <input
                v-model="newSetting.group_setting"
                type="text"
                required
                placeholder="e.g. General Setting, Email Setting"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Input Type</label>
              <select
                v-model="newSetting.content_input_type"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              >
                <option value="text">Text Input</option>
                <option value="textarea">Textarea</option>
                <option value="email">Email</option>
                <option value="number">Number</option>
                <option value="password">Password</option>
                <option value="upload_image">Upload Image</option>
                <option value="upload_file">Upload File</option>
                <option value="select">Select Options</option>
              </select>
            </div>

            <div v-if="newSetting.content_input_type === 'select'" class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Options (Comma separated)</label>
              <input
                v-model="newSetting.dataenum"
                type="text"
                placeholder="Option 1, Option 2, Option 3"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-stone-700 dark:text-stone-300">Helper Text (Optional)</label>
              <input
                v-model="newSetting.helper"
                type="text"
                placeholder="e.g. Recommended resolution 512x512px"
                class="w-full bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs text-stone-900 dark:text-stone-100 focus:ring-2 focus:ring-indigo-500"
              />
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-stone-100 dark:border-white/5">
              <button type="button" @click="isAddModalOpen = false" class="px-4 py-2 text-xs font-bold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-white/5 rounded-xl">
                Cancel
              </button>
              <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md">
                Add Key
              </button>
            </div>
          </form>

        </div>
      </div>
    </Transition>

  </InerminAppLayout>
</template>
