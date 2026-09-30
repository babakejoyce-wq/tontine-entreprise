<script setup lang="ts">
const api = useApi()
const modal = useState('modalCotisation', () => false)

const statut = ref<any>(null)
const cycles = ref<any[]>([])
const nouveauMois = ref('')
const erreur = ref('')
const manquants = ref<string[]>([])
const resultat = ref<any>(null)
const erreurModal = ref('')
const form = reactive({ membre_id: 0, periode: '' })

const fmt = (n: number) => Number(n).toLocaleString('fr-FR') + ' FCFA'
const msg = (e: any) => e?.data?.message ?? "Impossible de joindre l'API"
const initiales = (nom: string) => nom.split(' ').map(x => x[0]).join('').slice(0, 2).toUpperCase()

const membres = computed<any[]>(() => statut.value?.membres ?? [])
const nbRegle = computed(() => membres.value.filter(m => m.en_regle).length)
const enRetard = computed(() => membres.value.filter(m => !m.en_regle))
const totalDu = computed(() => membres.value.length * (membres.value[0]?.du ?? 0))
const collecte = computed(() => membres.value.reduce((s, m) => s + Math.min(m.paye, m.du), 0))
const pct = computed(() => (totalDu.value ? Math.round((collecte.value / totalDu.value) * 100) : 0))
const moisLabel = computed(() =>
  statut.value ? new Date(statut.value.mois + '-01').toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) : '')
const dejaRecus = computed(() => cycles.value.filter(c => c.statut === 'DISTRIBUE').map(c => c.beneficiaire_id))
const membreChoisi = computed(() => membres.value.find(m => m.id === form.membre_id))
const echeancesLibres = computed(() => membreChoisi.value?.echeances.filter((e: any) => !e.payee) ?? [])

function choisirPeriode() {
  form.periode = echeancesLibres.value[0]?.date ?? ''
}
watch(() => form.membre_id, choisirPeriode)
watch(modal, (ouvert) => {
  if (ouvert) {
    erreurModal.value = ''
    if (!membreChoisi.value) form.membre_id = membres.value[0]?.id ?? 0
    choisirPeriode()
  }
})

function ouvrirPour(m: any) {
  form.membre_id = m.id
  modal.value = true
}

async function charger() {
  cycles.value = await api<any[]>('/cycles')
  const ouvert = cycles.value.find(c => c.statut === 'OUVERT')
  statut.value = ouvert ? await api(`/cycles/${ouvert.mois}/statut`) : null
  choisirPeriode()
}

async function ouvrirMois() {
  erreur.value = ''; resultat.value = null
  try {
    await api('/cycles', { method: 'POST', body: { mois: nouveauMois.value } })
    await charger()
  } catch (e) { erreur.value = msg(e) }
}

async function cotiser() {
  erreurModal.value = ''
  try {
    await api('/cotisations', {
      method: 'POST',
      body: { membre_id: form.membre_id, mois: statut.value.mois, periode: form.periode },
    })
    modal.value = false
    await charger()
  } catch (e) { erreurModal.value = msg(e) }
}

async function designer() {
  erreur.value = ''; manquants.value = []
  try {
    resultat.value = await api(`/cycles/${statut.value.mois}/designer`, { method: 'POST' })
    await charger()
  } catch (e: any) {
    erreur.value = msg(e)
    manquants.value = e?.data?.manquants ?? []
  }
}

onMounted(charger)
</script>

<template>
  <div>
    <div v-if="erreur" class="alert err">
      <span>⚠️ {{ erreur }}<template v-if="manquants.length"> Manquants : {{ manquants.join(', ') }}.</template></span>
    </div>
    <div v-if="resultat" class="alert done">
      <span>🎉 Mois {{ resultat.mois }} distribué : bénéficiaire <strong>{{ resultat.beneficiaire?.nom }}</strong>,
        montant total <strong>{{ fmt(resultat.montant_total) }}</strong>.</span>
    </div>

    <!-- Aucun mois ouvert -->
    <div v-if="!statut" class="card">
      <h3>Aucun mois ouvert</h3>
      <p class="sub">Ouvrez un nouveau mois pour commencer à enregistrer les cotisations.</p>
      <div class="row" style="margin-top: 16px; max-width: 420px">
        <input type="month" v-model="nouveauMois" style="height: 42px; padding: 0 12px; border: 1px solid #CBD5E1; border-radius: .5rem" />
        <button class="btn primary" :disabled="!nouveauMois" @click="ouvrirMois">Ouvrir le mois</button>
      </div>
    </div>

    <template v-else>
      <div class="page-head">
        <h2>{{ moisLabel }} — Cycle mensuel</h2>
        <p><span class="badge ok">Cycle en cours</span></p>
      </div>

      <div class="grid-4">
        <div class="card stat">
          <div class="label">Membres actifs</div>
          <div class="value">{{ membres.length }}</div>
        </div>
        <div class="card stat">
          <div class="label">Membres en règle</div>
          <div class="value">{{ nbRegle }} <small>/ {{ membres.length }}</small></div>
        </div>
        <div class="card stat">
          <div class="label">Montant collecté</div>
          <div class="value">{{ fmt(collecte) }}</div>
        </div>
        <div class="card stat">
          <div class="label">Bénéficiaire prévu</div>
          <div class="value">{{ statut.beneficiaire_prevu?.nom ?? '—' }}</div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div>
            <h3>État de la tontine — {{ moisLabel }}</h3>
            <p class="sub">La désignation n'est possible que lorsque tous les membres ont cotisé.</p>
          </div>
          <span class="badge" :class="statut.peut_designer ? 'ok' : 'ko'">
            {{ statut.peut_designer ? 'Bénéficiaire débloqué' : 'Bénéficiaire bloqué' }}
          </span>
        </div>

        <div class="big">{{ nbRegle }} sur {{ membres.length }} membres en règle</div>
        <div class="progress"><div :style="{ width: pct + '%' }"></div></div>
        <p class="sub">{{ fmt(collecte) }} / {{ fmt(totalDu) }} ({{ pct }} %)</p>

        <div v-if="!statut.peut_designer" class="alert lock">
          <span>🔒 <strong>Verrouillage actif.</strong> Le bénéficiaire ne peut pas être désigné :
            {{ enRetard.map(m => m.nom).join(', ') }}
            {{ enRetard.length > 1 ? 'doivent' : 'doit' }} encore compléter {{ enRetard.length > 1 ? 'leurs' : 'sa' }} cotisation.</span>
        </div>
        <div v-else class="alert go">
          <span>🔓 <strong>Tous les membres ont cotisé.</strong> La désignation du bénéficiaire est autorisée.</span>
        </div>

        <div style="margin-top: 16px">
          <button class="btn" :class="statut.peut_designer ? 'emerald' : 'locked'" @click="designer">
            {{ statut.peut_designer ? '🔓' : '🔒' }} Désigner le bénéficiaire
          </button>
        </div>
      </div>

      <div class="grid-2">
        <div class="card">
          <div class="card-head">
            <h3>Suivi des cotisations du mois</h3>
            <span class="badge neutral">{{ membres.length }} adhérents</span>
          </div>
          <table class="tbl">
            <thead>
              <tr><th>Membre</th><th>Ordre</th><th>Fréquence</th><th>Statut</th><th>Payé / Dû</th><th>Échéances</th><th></th></tr>
            </thead>
            <tbody>
              <tr v-for="(m, i) in membres" :key="m.id">
                <td><div class="who"><span class="avatar">{{ initiales(m.nom) }}</span>{{ m.nom }}</div></td>
                <td>#{{ i + 1 }}</td>
                <td>{{ m.frequence.charAt(0) + m.frequence.slice(1).toLowerCase() }}</td>
                <td><span class="badge" :class="m.en_regle ? 'ok' : 'warn'">{{ m.en_regle ? 'Payé' : 'En attente' }}</span></td>
                <td>{{ fmt(m.paye) }} / {{ fmt(m.du) }}</td>
                <td>{{ m.echeances.filter((e: any) => e.payee).length }} / {{ m.echeances.length }}</td>
                <td><button class="btn ghost sm" :disabled="m.en_regle" @click="ouvrirPour(m)">Cotiser</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card">
          <div class="card-head"><h3>Tour de passage</h3></div>
          <ul class="timeline">
            <li v-for="(m, i) in membres" :key="m.id"
                :class="{ done: dejaRecus.includes(m.id), next: statut.beneficiaire_prevu?.id === m.id }">
              <span class="dot">{{ dejaRecus.includes(m.id) ? '✓' : i + 1 }}</span>
              <div>
                <strong>{{ m.nom }}</strong>
                <small>
                  {{ statut.beneficiaire_prevu?.id === m.id ? 'Prochain bénéficiaire' : dejaRecus.includes(m.id) ? 'Cagnotte reçue' : 'À venir' }}
                </small>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>

    <!-- Modal cotisation -->
    <div v-if="modal && statut" class="overlay" @click.self="modal = false">
      <div class="modal">
        <p class="eyebrow">● Sécurisation du cycle</p>
        <h3>Enregistrer une cotisation</h3>
        <p class="sub">Cycle de {{ moisLabel }}</p>

        <div v-if="erreurModal" class="alert err" style="margin-top: 16px">⚠️ {{ erreurModal }}</div>

        <div class="field">
          <label>Membre souscripteur</label>
          <select v-model.number="form.membre_id">
            <option v-for="(m, i) in membres" :key="m.id" :value="m.id">
              {{ m.nom }} (Ordre #{{ i + 1 }} – {{ m.en_regle ? 'En règle' : 'En attente' }})
            </option>
          </select>
        </div>

        <div class="row">
          <div class="field">
            <label>Mois de cotisation</label>
            <input :value="moisLabel" readonly />
          </div>
          <div class="field">
            <label>Montant de l'échéance</label>
            <input :value="membreChoisi ? fmt(membreChoisi.montant_echeance) : ''" readonly />
          </div>
        </div>

        <div class="field">
          <label>Échéance à payer</label>
          <select v-model="form.periode" :disabled="!echeancesLibres.length">
            <option v-if="!echeancesLibres.length" value="">Toutes les échéances sont payées</option>
            <option v-for="e in echeancesLibres" :key="e.date" :value="e.date">{{ e.date }}</option>
          </select>
        </div>

        <div class="actions">
          <button class="btn ghost" @click="modal = false">Annuler</button>
          <button class="btn emerald" :disabled="!form.periode" @click="cotiser">✓ Enregistrer la cotisation</button>
        </div>
      </div>
    </div>
  </div>
</template>