<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  app_name: String,
})

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/administrator/login')
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4 font-sans relative overflow-hidden">
    
    <!-- Ambient Background Glows -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6 relative z-10">
      
      <!-- Brand Header -->
      <div class="text-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold text-2xl mx-auto shadow-lg shadow-indigo-500/30">
          I
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-white">{{ app_name || 'Inermin Admin' }}</h2>
        <p class="text-xs text-slate-400">Sign in to access your admin dashboard</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="submit" class="space-y-4">
        
        <div class="space-y-1">
          <label class="block text-xs font-bold text-slate-300">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="admin@crudbooster.com"
            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
          />
          <p v-if="form.errors.email" class="text-[11px] text-rose-500 font-semibold mt-1">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold text-slate-300">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
          />
          <p v-if="form.errors.password" class="text-[11px] text-rose-500 font-semibold mt-1">{{ form.errors.password }}</p>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 disabled:opacity-50 transition duration-150"
        >
          Sign In
        </button>

      </form>

    </div>

  </div>
</template>
