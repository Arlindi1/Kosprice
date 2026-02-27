<script setup lang="ts">
import { computed } from 'vue'

type SelectOption = {
  label: string
  value: string | number
}

type SelectVariant = 'default' | 'pill'

const props = withDefaults(
  defineProps<{
    id?: string
    modelValue: string | number | null
    options: SelectOption[]
    disabled?: boolean
    placeholder?: string
    variant?: SelectVariant
  }>(),
  {
    id: '',
    disabled: false,
    placeholder: 'Select option',
    variant: 'default',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function onChange(event: Event): void {
  const target = event.target as HTMLSelectElement
  emit('update:modelValue', target.value)
}

const className = computed(() => {
  const base =
    'w-full appearance-none bg-white pr-10 text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200 disabled:cursor-not-allowed disabled:opacity-70'

  const byVariant: Record<SelectVariant, string> = {
    default: 'h-10 rounded-xl border border-slate-200 px-3 text-slate-700 hover:border-slate-300',
    pill: 'h-10 rounded-full border border-slate-200 bg-white/90 px-4 font-medium text-slate-700 shadow-sm hover:border-slate-300',
  }

  return [base, byVariant[props.variant]]
})
</script>

<template>
  <div class="relative">
    <select :id="props.id" :class="className" :value="props.modelValue ?? ''" :disabled="props.disabled" @change="onChange">
      <option value="" disabled>{{ props.placeholder }}</option>
      <option v-for="option in props.options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path
          fill-rule="evenodd"
          d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.512a.75.75 0 01-1.08 0l-4.25-4.512a.75.75 0 01.02-1.06z"
          clip-rule="evenodd"
        />
      </svg>
    </span>
  </div>
</template>
