export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',

  devtools: {
    enabled: true
  },

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api'
    }
  },

  css: ['@/assets/css/main.css'],

  app: {
    head: {
      title: 'TontineFlow',
      link: [
        {
          rel: 'preconnect',
          href: 'https://fonts.googleapis.com'
        },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap'
        },
        {
          rel: 'icon',
          type: 'image/svg+xml',
          href: '/logo.svg'
        },
        { rel: 'stylesheet', 
          href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css' 
        }
      ]
    }
  }
})