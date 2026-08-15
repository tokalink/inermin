<script setup>
import { ref } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  user: Object,
  action_url: String,
  tab: {
    type: String,
    default: 'profile',
  },
})

const activeTab = ref(props.tab || 'profile')
const photoPreview = ref(props.user.photo || '')
const photoFile = ref(null)

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  photo: null,
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    photoFile.value = file
    form.photo = file
    const reader = new FileReader()
    reader.onload = (event) => {
      photoPreview.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

const submitProfile = () => {
  form.post(props.action_url, {
    preserveScroll: true,
    onSuccess: () => {
      form.current_password = ''
      form.new_password = ''
      form.new_password_confirmation = ''
    },
  })
}
</script>

<template>
  <InerminAppLayout>
    <div class="max-w-4xl mx-auto space-y-6 font-sans">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight font-display">
            Account & Security Settings
          </h1>
          <p class="text-stone-500 dark:text-stone-400 text-xs mt-1">
            Manage your profile details, avatar, and password credentials
          </p>
        </div>

        <div class="flex items-center gap-2">
          <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-[rgb(var(--accent-rgb))]/10 text-[rgb(var(--accent-rgb))] border border-[rgb(var(--accent-rgb))]/20 flex items-center gap-2">
            <i class="bi bi-shield-check"></i>
            <span>{{ user.privilege_name }}</span>
          </span>
        </div>
      </div>

      <!-- Navigation Tabs Header -->
      <div class="flex border-b border-stone-200 dark:border-white/10 gap-2">
        <button
          @click="activeTab = 'profile'"
          :class="[
            'px-5 py-3 text-xs font-bold border-b-2 transition flex items-center gap-2',
            activeTab === 'profile'
              ? 'border-[rgb(var(--accent-rgb))] text-[rgb(var(--accent-rgb))]'
              : 'border-transparent text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-white'
          ]"
        >
          <i class="bi bi-person-bounding-box text-sm"></i>
          <span>Profile Information</span>
        </button>

        <button
          @click="activeTab = 'password'"
          :class="[
            'px-5 py-3 text-xs font-bold border-b-2 transition flex items-center gap-2',
            activeTab === 'password'
              ? 'border-[rgb(var(--accent-rgb))] text-[rgb(var(--accent-rgb))]'
              : 'border-transparent text-stone-500 dark:text-stone-400 hover:text-stone-800 dark:hover:text-white'
          ]"
        >
          <i class="bi bi-key-fill text-sm"></i>
          <span>Security & Password</span>
        </button>
      </div>

      <!-- Settings Form Card -->
      <form @submit.prevent="submitProfile" class="card rounded-3xl p-6 sm:p-8 space-y-6 shadow-xl border border-stone-200 dark:border-white/5">
        
        <!-- TAB 1: PROFILE INFO -->
        <div v-if="activeTab === 'profile'" class="space-y-6">
          
          <!-- Avatar Section -->
          <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200/50 dark:border-white/5">
            <div class="relative group shrink-0">
              <img
                :src="photoPreview || '/vendor/inermin/avatar.svg'"
                @error="(e) => e.target.src = '/vendor/inermin/avatar.svg'"
                class="w-24 h-24 rounded-2xl object-cover ring-4 ring-[rgba(var(--accent-rgb),0.25)] shadow-md transition-all group-hover:scale-105"
                alt="Avatar"
              />
              <label
                for="photo-upload"
                class="absolute inset-0 rounded-2xl bg-black/50 text-white flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer"
              >
                <i class="bi bi-camera-fill text-xl"></i>
                <span class="text-[10px] font-bold mt-1">Change</span>
              </label>
              <input
                id="photo-upload"
                type="file"
                accept="image/*"
                @change="handlePhotoChange"
                class="hidden"
              />
            </div>

            <div class="space-y-1 text-center sm:text-left flex-1">
              <h3 class="font-bold text-sm text-stone-900 dark:text-white">Profile Avatar</h3>
              <p class="text-xs text-stone-400">
                Upload a JPG, PNG or WEBP image. Max size 2MB.
              </p>
              <div class="pt-2 flex items-center justify-center sm:justify-start gap-2">
                <label
                  for="photo-upload"
                  class="px-3.5 py-2 rounded-xl bg-stone-200 dark:bg-white/10 text-stone-800 dark:text-stone-200 hover:bg-stone-300 dark:hover:bg-white/20 text-xs font-bold transition cursor-pointer flex items-center gap-2"
                >
                  <i class="bi bi-upload"></i>
                  <span>Upload Image</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Name & Email Inputs -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                Full Name <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <i class="bi bi-person absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="Enter your full name"
                  class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
                />
              </div>
              <p v-if="form.errors.name" class="text-rose-500 text-[11px] font-semibold mt-1">
                {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                Email Address <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <i class="bi bi-envelope absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="name@company.com"
                  class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
                />
              </div>
              <p v-if="form.errors.email" class="text-rose-500 text-[11px] font-semibold mt-1">
                {{ form.errors.email }}
              </p>
            </div>
          </div>

          <!-- Account Meta Info -->
          <div class="p-4 rounded-2xl bg-stone-50 dark:bg-white/[0.02] border border-stone-200/50 dark:border-white/5 flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
            <div class="flex items-center gap-2">
              <i class="bi bi-info-circle-fill text-[rgb(var(--accent-rgb))]"></i>
              <span>Account Member Since:</span>
            </div>
            <span class="font-bold text-stone-800 dark:text-stone-200">{{ user.created_at }}</span>
          </div>

        </div>

        <!-- TAB 2: SECURITY & PASSWORD -->
        <div v-if="activeTab === 'password'" class="space-y-6">
          <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-medium flex items-center gap-3">
            <i class="bi bi-shield-lock-fill text-base shrink-0"></i>
            <span>Leave password fields blank if you do not wish to change your current password.</span>
          </div>

          <div class="space-y-4">
            <!-- Current Password -->
            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                Current Password
              </label>
              <div class="relative">
                <i class="bi bi-lock absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                <input
                  v-model="form.current_password"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  placeholder="Enter current password"
                  class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
                />
                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-3 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 text-sm"
                >
                  <i :class="showCurrentPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                </button>
              </div>
              <p v-if="form.errors.current_password" class="text-rose-500 text-[11px] font-semibold mt-1">
                {{ form.errors.current_password }}
              </p>
            </div>

            <!-- New Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
              <div class="space-y-2">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                  New Password
                </label>
                <div class="relative">
                  <i class="bi bi-key absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                  <input
                    v-model="form.new_password"
                    :type="showNewPassword ? 'text' : 'password'"
                    placeholder="Min 6 characters"
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-3 top-3 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 text-sm"
                  >
                    <i :class="showNewPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                  </button>
                </div>
                <p v-if="form.errors.new_password" class="text-rose-500 text-[11px] font-semibold mt-1">
                  {{ form.errors.new_password }}
                </p>
              </div>

              <!-- Confirm New Password -->
              <div class="space-y-2">
                <label class="block text-xs font-bold text-stone-700 dark:text-stone-300">
                  Confirm New Password
                </label>
                <div class="relative">
                  <i class="bi bi-check2-circle absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                  <input
                    v-model="form.new_password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="Re-enter new password"
                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-stone-200 dark:border-white/10 bg-white dark:bg-[#15130f] text-stone-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-[rgb(var(--accent-rgb))] focus:outline-none transition"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-3 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 text-sm"
                  >
                    <i :class="showConfirmPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                  </button>
                </div>
                <p v-if="form.errors.new_password_confirmation" class="text-rose-500 text-[11px] font-semibold mt-1">
                  {{ form.errors.new_password_confirmation }}
                </p>
              </div>
            </div>

          </div>
        </div>

        <!-- Form Submit Actions Footer -->
        <div class="pt-4 border-t border-stone-200/60 dark:border-white/5 flex items-center justify-end gap-3">
          <Link
            :href="adminPath"
            class="px-5 py-2.5 rounded-xl bg-stone-100 dark:bg-white/5 hover:bg-stone-200 dark:hover:bg-white/10 text-stone-700 dark:text-stone-300 text-xs font-bold transition"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 rounded-xl text-white font-bold text-xs shadow-lg transition flex items-center gap-2 disabled:opacity-50"
            style="background: linear-gradient(135deg, rgb(var(--accent-soft)), rgb(var(--accent-deep)));"
          >
            <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-check-lg text-sm"></i>
            <span>Save Profile & Credentials</span>
          </button>
        </div>

      </form>
    </div>
  </InerminAppLayout>
</template>
