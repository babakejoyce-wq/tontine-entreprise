type Options = { titre?: string; message: string; ton?: 'normal' | 'danger' }

let resolveur: ((ok: boolean) => void) | null = null

export const useConfirm = () => {
  const etat = useState('confirmation', () => ({
    ouvert: false, titre: '', message: '', ton: 'normal',
  }))

  // Renvoie une promesse : true si OK, false si Annuler
  const confirmer = (o: Options) =>
    new Promise<boolean>((resolve) => {
      resolveur = resolve
      etat.value = { ouvert: true, titre: o.titre ?? 'Confirmation', message: o.message, ton: o.ton ?? 'normal' }
    })

  const repondre = (ok: boolean) => {
    etat.value = { ...etat.value, ouvert: false }
    resolveur?.(ok)
    resolveur = null
  }

  return { etat, confirmer, repondre }
}