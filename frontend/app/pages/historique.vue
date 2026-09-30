<script setup lang="ts">
const api = useApi()
const cycles = ref<any[]>([])
const fmt = (n: number) => Number(n).toLocaleString('fr-FR') + ' FCFA'
const moisLabel = (m: string) => new Date(m + '-01').toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
const total = computed(() => cycles.value.reduce((s, c) => s + Number(c.montant_total), 0))

onMounted(async () => {
  cycles.value = await api<any[]>('/cycles?statut=DISTRIBUE')
})
</script>

<template>
  <div>
    <div class="page-head">
      <h2>Historique des distributions</h2>
      <p>Mois déjà distribués, avec le bénéficiaire et le montant total.</p>
    </div>

    <div class="grid-4" style="grid-template-columns: repeat(2, 1fr)">
      <div class="card stat">
        <div class="label">Total distribué</div>
        <div class="value">{{ fmt(total) }}</div>
      </div>
      <div class="card stat">
        <div class="label">Mois distribués</div>
        <div class="value">{{ cycles.length }}</div>
      </div>
    </div>

    <div class="card">
      <p v-if="!cycles.length" class="sub">Aucun mois distribué pour le moment.</p>
      <table v-else class="tbl">
        <thead><tr><th>Mois</th><th>Bénéficiaire</th><th>Montant total</th><th>Clôturé le</th><th>Statut</th></tr></thead>
        <tbody>
          <tr v-for="c in cycles" :key="c.id">
            <td style="text-transform: capitalize; font-weight: 600">{{ moisLabel(c.mois) }}</td>
            <td>{{ c.beneficiaire?.nom }}</td>
            <td>{{ fmt(c.montant_total) }}</td>
            <td>{{ c.date_cloture }}</td>
            <td><span class="badge ok">Distribué</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>