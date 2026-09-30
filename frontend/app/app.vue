<script setup lang="ts">
const route = useRoute()
const modal = useState('modalCotisation', () => false)
const menuOuvert = useState('menuOuvert', () => true)

const liens = [
  { to: '/', label: 'Tableau de bord', icon: 'fa-table-cells-large' },
  { to: '/membres', label: 'Membres', icon: 'fa-users' },
  { to: '/historique', label: 'Historique', icon: 'fa-clock-rotate-left' },
]
const titre = computed(() => liens.find(l => l.to === route.path)?.label ?? '')

const petitEcran = () => window.innerWidth < 768
onMounted(() => { if (petitEcran()) menuOuvert.value = false })
watch(() => route.path, () => { if (petitEcran()) menuOuvert.value = false })
</script>

<template>
  <div class="app">
    <aside class="sidebar" :class="{ closed: !menuOuvert }">
      <div class="brand">
        <img src="/logo.svg" alt="" width="36" height="36" />
        <span>TontineFlow</span>
      </div>
      <p class="nav-title">Navigation</p>
      <NuxtLink v-for="l in liens" :key="l.to" :to="l.to" class="nav-link">
        <i class="fa-solid" :class="l.icon"></i>{{ l.label }}
      </NuxtLink>
    </aside>
    <div v-if="menuOuvert" class="backdrop" @click="menuOuvert = false"></div>

    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button class="icon-btn" :title="menuOuvert ? 'Fermer le menu' : 'Ouvrir le menu'" @click="menuOuvert = !menuOuvert">
            <i class="fa-solid" :class="menuOuvert ? 'fa-angles-left' : 'fa-bars'"></i>
          </button>
          <h1>{{ titre }}</h1>
        </div>
        <button v-if="route.path === '/'" class="btn emerald" @click="modal = true">
          <i class="fa-solid fa-circle-plus"></i>Enregistrer une cotisation
        </button>
      </header>
      <main class="content"><NuxtPage /></main>
    </div>

    <ConfirmDialog />
  </div>
</template>