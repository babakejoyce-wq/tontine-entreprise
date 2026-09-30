<script setup lang="ts">
const route = useRoute()
const modal = useState('modalCotisation', () => false)

const liens = [
  { to: '/', label: 'Tableau de bord', icon: '▦' },
  { to: '/membres', label: 'Membres', icon: '👥' },
  { to: '/historique', label: 'Historique', icon: '🕘' },
]
const titre = computed(() => liens.find(l => l.to === route.path)?.label ?? '')
</script>

<template>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">
        <img src="/logo.svg" alt="" width="36" height="36" />
        <span>TontineFlow</span>
      </div>
      <p class="nav-title">Navigation</p>
      <NuxtLink v-for="l in liens" :key="l.to" :to="l.to" class="nav-link">
        <span>{{ l.icon }}</span>{{ l.label }}
      </NuxtLink>
    </aside>

    <div class="main">
      <header class="topbar">
        <h1>{{ titre }}</h1>
        <button v-if="route.path === '/'" class="btn emerald" @click="modal = true">
          ＋ Enregistrer une cotisation
        </button>
      </header>
      <main class="content"><NuxtPage /></main>
    </div>
  </div>
</template>