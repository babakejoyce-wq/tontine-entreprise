<script setup lang="ts">
const { etat, repondre } = useConfirm()
</script>

<template>
  <div v-if="etat.ouvert" class="overlay confirm-overlay" @click.self="repondre(false)">
    <div class="modal confirm">
      <div class="confirm-icon" :class="{ danger: etat.ton === 'danger' }">
        <i class="fa-solid" :class="etat.ton === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-question'"></i>
      </div>
      <h3>{{ etat.titre }}</h3>
      <p class="sub">{{ etat.message }}</p>
      <div class="actions">
        <button class="btn ghost" @click="repondre(false)">Annuler</button>
        <button class="btn primary" @click="repondre(true)">OK</button>
      </div>
    </div>
  </div>
</template>