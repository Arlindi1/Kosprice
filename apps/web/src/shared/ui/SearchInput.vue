<script setup lang="ts">
import Button from '@/shared/ui/Button.vue'
import Input from '@/shared/ui/Input.vue'

const props = withDefaults(
  defineProps<{
    modelValue: string
    placeholder?: string
    disabled?: boolean
  }>(),
  {
    placeholder: 'Search...',
    disabled: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function clear(): void {
  emit('update:modelValue', '')
}
</script>

<template>
  <div class="relative w-full">
    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path
          fill-rule="evenodd"
          d="M8.5 3a5.5 5.5 0 013.84 9.433l3.613 3.614a.75.75 0 11-1.06 1.06l-3.614-3.613A5.5 5.5 0 118.5 3zm-4 5.5a4 4 0 108 0 4 4 0 00-8 0z"
          clip-rule="evenodd"
        />
      </svg>
    </span>

    <Input
      :model-value="props.modelValue"
      :placeholder="props.placeholder"
      :disabled="props.disabled"
      type="search"
      class="pl-9 pr-10"
      @update:model-value="(value) => emit('update:modelValue', value)"
    />

    <div v-if="props.modelValue" class="absolute inset-y-0 right-1 flex items-center">
      <Button variant="ghost" size="sm" @click="clear">Clear</Button>
    </div>
  </div>
</template>

