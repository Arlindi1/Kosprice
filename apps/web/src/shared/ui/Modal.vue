<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    panelClass?: string
    closeOnBackdrop?: boolean
  }>(),
  {
    panelClass: 'max-w-[920px]',
    closeOnBackdrop: true,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const panelRef = ref<HTMLElement | null>(null)

function close(): void {
  emit('update:modelValue', false)
}

function onBackdropMouseDown(): void {
  if (props.closeOnBackdrop) {
    close()
  }
}

function getFocusableElements(): HTMLElement[] {
  if (!panelRef.value) {
    return []
  }

  const selectors = [
    'a[href]',
    'button:not([disabled])',
    'textarea:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
  ]

  return Array.from(
    panelRef.value.querySelectorAll<HTMLElement>(selectors.join(',')),
  ).filter((element) => !element.hasAttribute('disabled') && !element.getAttribute('aria-hidden'))
}

function handleKeydown(event: KeyboardEvent): void {
  if (!props.modelValue) {
    return
  }

  if (event.key === 'Escape') {
    event.preventDefault()
    close()
    return
  }

  if (event.key !== 'Tab') {
    return
  }

  const focusables = getFocusableElements()
  if (focusables.length === 0) {
    event.preventDefault()
    panelRef.value?.focus()
    return
  }

  const first = focusables[0]
  const last = focusables[focusables.length - 1]
  if (!first || !last) {
    event.preventDefault()
    panelRef.value?.focus()
    return
  }

  const activeElement = document.activeElement as HTMLElement | null

  if (event.shiftKey && activeElement === first) {
    event.preventDefault()
    last.focus()
    return
  }

  if (!event.shiftKey && activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

watch(
  () => props.modelValue,
  async (isOpen) => {
    if (isOpen) {
      document.body.classList.add('overflow-hidden')
      document.addEventListener('keydown', handleKeydown)

      await nextTick()
      const focusables = getFocusableElements()
      ;(focusables[0] ?? panelRef.value)?.focus()
      return
    }

    document.body.classList.remove('overflow-hidden')
    document.removeEventListener('keydown', handleKeydown)
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  document.body.classList.remove('overflow-hidden')
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="props.modelValue" class="fixed inset-0 z-[70]">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" @mousedown.self="onBackdropMouseDown" />

        <div class="pointer-events-none relative flex min-h-full items-center justify-center p-4 sm:p-6">
          <div
            ref="panelRef"
            tabindex="-1"
            class="pointer-events-auto w-full rounded-3xl border border-slate-200 bg-white shadow-2xl transition-[max-width] duration-300 ease-out"
            :class="props.panelClass"
            @mousedown.stop
          >
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.22s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
