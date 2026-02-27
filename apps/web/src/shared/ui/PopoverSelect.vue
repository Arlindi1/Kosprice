<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'

type PopoverOption = {
  label: string
  value: number | string
}

const props = withDefaults(
  defineProps<{
    modelValue: number | string | null
    options: PopoverOption[]
    label: string
    placeholder?: string
    disabled?: boolean
  }>(),
  {
    placeholder: 'Select option',
    disabled: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: number | string]
}>()

const isOpen = ref(false)
const rootRef = ref<HTMLElement | null>(null)

const selectedLabel = computed(() => {
  const selected = props.options.find((option) => String(option.value) === String(props.modelValue))
  return selected?.label ?? props.placeholder
})

function close(): void {
  isOpen.value = false
}

function toggle(): void {
  if (props.disabled) {
    return
  }

  isOpen.value = !isOpen.value
}

function selectOption(value: number | string): void {
  emit('update:modelValue', value)
  close()
}

function handleDocumentMouseDown(event: MouseEvent): void {
  if (!isOpen.value) {
    return
  }

  const target = event.target as Node
  if (!rootRef.value?.contains(target)) {
    close()
  }
}

function handleDocumentKeydown(event: KeyboardEvent): void {
  if (!isOpen.value) {
    return
  }

  if (event.key === 'Escape') {
    event.preventDefault()
    close()
  }
}

watch(
  () => isOpen.value,
  (open) => {
    if (open) {
      document.addEventListener('mousedown', handleDocumentMouseDown)
      document.addEventListener('keydown', handleDocumentKeydown)
      return
    }

    document.removeEventListener('mousedown', handleDocumentMouseDown)
    document.removeEventListener('keydown', handleDocumentKeydown)
  },
)

watch(
  () => props.disabled,
  (disabled) => {
    if (disabled) {
      close()
    }
  },
)

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleDocumentMouseDown)
  document.removeEventListener('keydown', handleDocumentKeydown)
})
</script>

<template>
  <div ref="rootRef" class="relative w-full">
    <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ props.label }}</p>

    <button
      type="button"
      class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-3.5 text-left text-sm font-medium text-slate-700 transition hover:border-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
      :disabled="props.disabled"
      @click="toggle"
    >
      <span class="truncate">{{ selectedLabel }}</span>
      <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path
          fill-rule="evenodd"
          d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.512a.75.75 0 01-1.08 0l-4.25-4.512a.75.75 0 01.02-1.06z"
          clip-rule="evenodd"
        />
      </svg>
    </button>

    <Transition name="popover-fade">
      <div
        v-if="isOpen"
        class="absolute z-30 mt-2 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
      >
        <ul class="max-h-64 overflow-auto p-1.5">
          <li v-for="option in props.options" :key="option.value">
            <button
              type="button"
              class="w-full rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200"
              :class="String(option.value) === String(props.modelValue) ? 'bg-brand-50 text-brand-700' : 'text-slate-700'"
              @click="selectOption(option.value)"
            >
              {{ option.label }}
            </button>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.popover-fade-enter-active,
.popover-fade-leave-active {
  transition: all 0.14s ease;
}

.popover-fade-enter-from,
.popover-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
