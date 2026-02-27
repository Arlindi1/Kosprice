import { computed, ref, watch } from 'vue'

import { useCityStore } from '@/features/city/store/useCityStore'
import { getProductsCatalog, isAbortError, toErrorMessage } from '@/lib/api'

function formatDateLabel(date: string | null): string {
  if (!date) {
    return 'today'
  }

  const today = new Date().toISOString().slice(0, 10)
  if (date === today) {
    return 'today'
  }

  return new Date(`${date}T00:00:00Z`).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

export function useLastUpdatedIndicator() {
  const cityStore = useCityStore()
  const newestTimestamp = ref<string | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  let loadController: AbortController | null = null

  const label = computed(() => `Last updated ${formatDateLabel(newestTimestamp.value)}`)

  async function load(cityId: number | null, force = false): Promise<void> {
    loadController?.abort()

    if (cityId === null) {
      newestTimestamp.value = null
      error.value = null
      isLoading.value = false
      loadController = null
      return
    }

    const controller = new AbortController()
    loadController = controller

    isLoading.value = true
    error.value = null

    try {
      const catalog = await getProductsCatalog(cityId, {
        force,
        signal: controller.signal,
      })

      if (loadController !== controller) {
        return
      }

      newestTimestamp.value = catalog.meta?.recorded_at ?? null
    } catch (caughtError) {
      if (isAbortError(caughtError)) {
        return
      }

      error.value = toErrorMessage(caughtError)
      newestTimestamp.value = null
    } finally {
      if (loadController === controller) {
        loadController = null
        isLoading.value = false
      }
    }
  }

  watch(
    () => cityStore.activeCityId,
    (cityId) => {
      void load(cityId)
    },
    { immediate: true },
  )

  return {
    label,
    newestTimestamp,
    isLoading,
    error,
    refresh: (force = false) => load(cityStore.activeCityId, force),
  }
}
