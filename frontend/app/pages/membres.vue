<script setup lang="ts">
const api = useApi()
const membres = ref<any[]>([])
const erreur = ref('')
const form = reactive({ nom: '', telephone: '', ordre_tour: 1, frequence: 'MENSUELLE' })

const initiales = (nom: string) => nom.split(' ').map(x => x[0]).join('').slice(0, 2).toUpperCase()
const libelle: Record<string, string> = { JOURNALIERE: 'Journalière', HEBDOMADAIRE: 'Hebdomadaire', MENSUELLE: 'Mensuelle' }

async function charger() {
  membres.value = await api<any[]>('/membres')
  form.ordre_tour = membres.value.length + 1
}

async function ajouter() {
  erreur.value = ''
  try {
    await api('/membres', { method: 'POST', body: { ...form } })
    form.nom = ''; form.telephone = ''
    await charger()
  } catch (e: any) {
    erreur.value = e?.data?.message ?? "Impossible de joindre l'API"
  }
}

onMounted(charger)
</script>

<template>
  <div>
    <div class="page-head">
      <h2>Membres</h2>
      <p>{{ membres.length }} membres actifs dans la tontine d'entreprise, classés par ordre de passage.</p>
    </div>

    <div class="card">
      <table class="tbl">
        <thead><tr><th>Ordre</th><th>Membre</th><th>Téléphone</th><th>Cotisation</th></tr></thead>
        <tbody>
          <tr v-for="m in membres" :key="m.id">
            <td><span class="badge ok">#{{ m.ordre_tour }}</span></td>
            <td><div class="who"><span class="avatar">{{ initiales(m.nom) }}</span>{{ m.nom }}</div></td>
            <td>{{ m.telephone }}</td>
            <td><span class="badge neutral">{{ libelle[m.frequence] }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Ajouter un membre</h3>
      <div v-if="erreur" class="alert err" style="margin-top: 12px">⚠️ {{ erreur }}</div>
      <div class="inline-form" style="margin-top: 16px">
        <div class="field"><label>Nom</label><input v-model="form.nom" placeholder="Nom complet" /></div>
        <div class="field"><label>Téléphone</label><input v-model="form.telephone" placeholder="90 00 00 00" /></div>
        <div class="field"><label>Rang</label><input v-model.number="form.ordre_tour" type="number" min="1" /></div>
        <div class="field">
          <label>Cotisation</label>
          <select v-model="form.frequence">
            <option value="JOURNALIERE">Journalière</option>
            <option value="HEBDOMADAIRE">Hebdomadaire</option>
            <option value="MENSUELLE">Mensuelle</option>
          </select>
        </div>
        <span></span>
        <button class="btn primary" :disabled="!form.nom || !form.telephone" @click="ajouter">+ Ajouter</button>
      </div>
    </div>
  </div>
</template>