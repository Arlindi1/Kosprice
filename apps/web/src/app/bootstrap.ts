import { createPinia } from 'pinia'
import { createApp } from 'vue'

import AppLayout from '@/app/AppLayout.vue'
import { router } from '@/app/router'
import '@/app/app.css'

export function bootstrap(): void {
  const app = createApp(AppLayout)

  app.use(createPinia())
  app.use(router)
  app.mount('#app')
}

