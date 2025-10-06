<script setup>
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import { useAuthStore } from './stores/auth'

const auth = useAuthStore()
onMounted(() => {
  // Try to populate user if we already have a token
  if (auth.token && !auth.user) auth.fetchUser()
})
</script>

<template>
  <div class="container-page">
    <AppHeader />
    <main class="py-6">
      <RouterView />
    </main>
  </div>
  
</template>

<style scoped></style>
