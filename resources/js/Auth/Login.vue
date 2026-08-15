<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
  app_name: String,
  app_logo: String,
  login_style: {
    type: String,
    default: 'glassmorphism',
  },
  login_background_image: String,
})

const page = usePage()
const adminPath = computed(() => '/' + (page.props.admin_path || 'administrator').replace(/^\//, ''))

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post(adminPath.value + '/login')
}

const currentStyle = computed(() => {
  const allowed = ['glassmorphism', 'split-screen', 'minimal-clean', 'gradient-glow']
  return allowed.includes(props.login_style) ? props.login_style : 'glassmorphism'
})
</script>

<template>
  <div
    class="min-h-screen text-stone-100 font-sans relative overflow-hidden bg-[#0c0b09] selection:bg-amber-500 selection:text-white"
    :style="login_background_image ? { backgroundImage: `url('${login_background_image}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}"
  >
    <!-- Background Dark Overlay when background image is present -->
    <div v-if="login_background_image" class="absolute inset-0 bg-black/75 backdrop-blur-xs z-0"></div>

    <!-- ========================================== -->
    <!-- STYLE 1: AETHER GLASSMORPHISM (DEFAULT)     -->
    <!-- ========================================== -->
    <div
      v-if="currentStyle === 'glassmorphism'"
      class="min-h-screen flex items-center justify-center p-4 relative z-10"
    >
      <!-- Ambient Accent Glow Spheres -->
      <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <div class="w-full max-w-md bg-[#15130f]/85 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 sm:p-10 shadow-2xl space-y-6 relative">
        
        <!-- Header -->
        <div class="text-center space-y-3">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-700 flex items-center justify-center text-white font-bold text-2xl font-display mx-auto shadow-xl shadow-amber-500/25">
            <img v-if="app_logo" :src="app_logo" class="w-9 h-9 object-contain" alt="Logo" />
            <span v-else>Æ</span>
          </div>
          <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-white font-display">
              {{ app_name || 'Inermin Admin' }}
            </h2>
            <p class="text-xs text-stone-400 mt-1 font-medium">Executive Console Authentication</p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-stone-300">Email Address</label>
            <div class="relative flex items-center">
              <i class="bi bi-envelope absolute left-3.5 text-stone-400 text-sm"></i>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="admin@inermin.com"
                class="w-full bg-white/5 border border-white/10 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
              />
            </div>
            <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-stone-300">Password</label>
            <div class="relative flex items-center">
              <i class="bi bi-lock absolute left-3.5 text-stone-400 text-sm"></i>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full bg-white/5 border border-white/10 rounded-2xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
              />
            </div>
            <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.password }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 rounded-2xl font-bold text-xs text-white shadow-xl transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
            style="background: linear-gradient(135deg, #f59e0b, #b45309); box-shadow: 0 8px 25px -6px rgba(245, 158, 11, 0.4);"
          >
            <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-box-arrow-in-right text-base"></i>
            <span>Sign In to Console</span>
          </button>
        </form>

        <div class="text-center border-t border-white/5 pt-4">
          <p class="text-[11px] text-stone-500">&copy; {{ new Date().getFullYear() }} Aether Console &bull; Inermin SPA Admin</p>
        </div>

      </div>
    </div>

    <!-- ========================================== -->
    <!-- STYLE 2: SPLIT SCREEN (LEFT BRAND / RIGHT FORM) -->
    <!-- ========================================== -->
    <div
      v-else-if="currentStyle === 'split-screen'"
      class="min-h-screen flex flex-col lg:flex-row relative z-10"
    >
      <!-- Left Branding Banner -->
      <div class="lg:w-1/2 p-8 sm:p-16 flex flex-col justify-between relative bg-gradient-to-br from-amber-900/60 via-stone-900 to-[#0c0b09] border-b lg:border-b-0 lg:border-r border-white/10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-500 flex items-center justify-center text-white font-bold text-xl font-display shadow-lg shadow-amber-500/30">
            <img v-if="app_logo" :src="app_logo" class="w-6 h-6 object-contain" alt="Logo" />
            <span v-else>Æ</span>
          </div>
          <span class="font-display font-extrabold text-lg text-white tracking-tight">{{ app_name || 'Inermin Admin' }}</span>
        </div>

        <div class="my-12 space-y-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-[11px] font-bold uppercase tracking-wider">
            <span>Executive SPA Portal</span>
          </div>
          <h1 class="font-display text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
            High Performance Management Dashboard
          </h1>
          <p class="text-stone-300 text-sm font-medium leading-relaxed max-w-md">
            Streamlined Single Page Application workspace powered by Inertia.js, Vue 3, and CRUDBooster Reborn.
          </p>
        </div>

        <div class="flex items-center gap-6 text-xs text-stone-400 font-medium">
          <span class="flex items-center gap-2"><i class="bi bi-shield-check text-amber-500 text-sm"></i> RBAC Security</span>
          <span class="flex items-center gap-2"><i class="bi bi-lightning-charge text-amber-500 text-sm"></i> Inertia SPA</span>
          <span class="flex items-center gap-2"><i class="bi bi-cpu text-amber-500 text-sm"></i> FastExcel</span>
        </div>
      </div>

      <!-- Right Form Panel -->
      <div class="lg:w-1/2 p-8 sm:p-16 flex items-center justify-center bg-[#15130f]/90 backdrop-blur-xl">
        <div class="w-full max-w-md space-y-6">
          <div>
            <h2 class="text-2xl font-bold text-white font-display">Welcome Back 👋</h2>
            <p class="text-xs text-stone-400 mt-1">Please enter your credentials to log in</p>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-stone-300">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="admin@inermin.com"
                class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
              />
              <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-stone-300">Password</label>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
              />
              <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.password }}</p>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-3 rounded-2xl font-bold text-xs text-white shadow-xl transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
              style="background: linear-gradient(135deg, #f59e0b, #b45309);"
            >
              <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
              <i v-else class="bi bi-box-arrow-in-right text-base"></i>
              <span>Sign In to Account</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- STYLE 3: MINIMAL CLEAN                    -->
    <!-- ========================================== -->
    <div
      v-else-if="currentStyle === 'minimal-clean'"
      class="min-h-screen flex items-center justify-center p-4 relative z-10"
    >
      <div class="w-full max-w-sm bg-stone-900 border border-stone-800 rounded-2xl p-8 shadow-xl space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-stone-800 border border-stone-700 flex items-center justify-center text-amber-500 font-bold text-lg font-display">
            Æ
          </div>
          <div>
            <h2 class="text-base font-bold text-white font-display">{{ app_name || 'Inermin Admin' }}</h2>
            <p class="text-[11px] text-stone-400">Sign in to your account</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-1">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="admin@inermin.com"
              class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-stone-600 focus:border-amber-500 focus:outline-none transition"
            />
            <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-1">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-stone-400">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-stone-600 focus:border-amber-500 focus:outline-none transition"
            />
            <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.password }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2.5 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-600 text-stone-950 transition disabled:opacity-50"
          >
            Sign In
          </button>
        </form>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- STYLE 4: CYBER GRADIENT MESH GLOW          -->
    <!-- ========================================== -->
    <div
      v-else-if="currentStyle === 'gradient-glow'"
      class="min-h-screen flex items-center justify-center p-4 relative z-10"
    >
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-amber-600/30 via-purple-900/20 to-[#0c0b09]"></div>

      <div class="w-full max-w-md bg-[#15130f]/90 border border-amber-500/30 rounded-3xl p-8 sm:p-10 shadow-[0_0_50px_rgba(245,158,11,0.25)] space-y-6 relative z-10">
        <div class="text-center space-y-2">
          <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-amber-500 via-rose-500 to-violet-600 flex items-center justify-center text-white font-black text-3xl font-display mx-auto shadow-xl ring-4 ring-amber-500/20">
            Æ
          </div>
          <h2 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-rose-400 to-violet-400 font-display">
            {{ app_name || 'Inermin Admin' }}
          </h2>
          <p class="text-xs text-stone-400">Cyber Console Authentication</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-stone-300">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="admin@inermin.com"
              class="w-full bg-stone-900/80 border border-amber-500/30 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
            />
            <p v-if="form.errors.email" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-stone-300">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full bg-stone-900/80 border border-amber-500/30 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
            />
            <p v-if="form.errors.password" class="text-[11px] text-rose-400 font-semibold mt-1">{{ form.errors.password }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 rounded-2xl font-black text-xs text-white shadow-xl transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
            style="background: linear-gradient(135deg, #f59e0b, #ef4444, #8b5cf6);"
          >
            <i v-if="form.processing" class="bi bi-arrow-repeat animate-spin"></i>
            <i v-else class="bi bi-shield-lock-fill"></i>
            <span>Authenticate</span>
          </button>
        </form>
      </div>
    </div>

  </div>
</template>
