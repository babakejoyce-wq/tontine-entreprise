export const useApi = () => {
  const base = useRuntimeConfig().public.apiBase as string
  return <T = any>(path: string, opts: any = {}) =>
    $fetch<T>(base + path, {
      ...opts,
      headers: { Accept: 'application/json', ...(opts.headers || {}) },
    })
}