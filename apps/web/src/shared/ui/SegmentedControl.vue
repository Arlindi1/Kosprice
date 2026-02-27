<script setup lang="ts">
type SegmentOption = {
  label: string
  value: string | number
}

const props = defineProps<{
  modelValue: string | number
  options: SegmentOption[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()
</script>

<template>
  <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
    <button
      v-for="option in props.options"
      :key="option.value"
      type="button"
      class="min-w-[52px] rounded-lg px-3 py-1.5 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200"
      :class="
        props.modelValue === option.value
          ? 'bg-brand-600 text-white shadow-sm'
          : 'text-slate-600 hover:bg-slate-100'
      "
      @click="emit('update:modelValue', option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>
