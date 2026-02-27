import { computed, onBeforeUnmount, ref, watch, type ComputedRef } from 'vue'

type UseAutoplayCarouselOptions = {
  enabled: ComputedRef<boolean>
  onTick: () => void
  delayMs?: number
  resumeAfterInteractionMs?: number
}

export function useAutoplayCarousel(options: UseAutoplayCarouselOptions) {
  const delayMs = options.delayMs ?? 3500
  const resumeAfterInteractionMs = options.resumeAfterInteractionMs ?? 7000

  const isHovered = ref(false)
  const isUserInteracting = ref(false)

  const isPaused = computed(
    () => !options.enabled.value || isHovered.value || isUserInteracting.value,
  )

  let intervalId: ReturnType<typeof window.setInterval> | null = null
  let interactionTimeoutId: ReturnType<typeof window.setTimeout> | null = null

  function stopInterval(): void {
    if (intervalId !== null) {
      window.clearInterval(intervalId)
      intervalId = null
    }
  }

  function clearInteractionTimeout(): void {
    if (interactionTimeoutId !== null) {
      window.clearTimeout(interactionTimeoutId)
      interactionTimeoutId = null
    }
  }

  function setHovered(value: boolean): void {
    isHovered.value = value
  }

  function markUserInteraction(): void {
    if (typeof window === 'undefined') {
      return
    }

    isUserInteracting.value = true
    clearInteractionTimeout()

    interactionTimeoutId = window.setTimeout(() => {
      isUserInteracting.value = false
      interactionTimeoutId = null
    }, resumeAfterInteractionMs)
  }

  watch(
    isPaused,
    (paused) => {
      if (typeof window === 'undefined') {
        return
      }

      if (paused) {
        stopInterval()
        return
      }

      if (intervalId !== null) {
        return
      }

      intervalId = window.setInterval(() => {
        if (!isPaused.value) {
          options.onTick()
        }
      }, delayMs)
    },
    { immediate: true },
  )

  watch(
    () => options.enabled.value,
    (enabled) => {
      if (enabled) {
        return
      }

      isUserInteracting.value = false
      clearInteractionTimeout()
    },
  )

  onBeforeUnmount(() => {
    stopInterval()
    clearInteractionTimeout()
  })

  return {
    isPaused,
    setHovered,
    markUserInteraction,
  }
}
