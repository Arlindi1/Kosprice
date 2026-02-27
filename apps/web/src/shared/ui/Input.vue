<script setup lang="ts">
type InputType = 'text' | 'search' | 'number' | 'email'

const props = withDefaults(
  defineProps<{
    modelValue: string | number
    type?: InputType
    placeholder?: string
    disabled?: boolean
    id?: string
  }>(),
  {
    type: 'text',
    placeholder: '',
    disabled: false,
    id: '',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function onInput(event: Event): void {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <input
    :id="props.id"
    :value="props.modelValue"
    :type="props.type"
    :placeholder="props.placeholder"
    :disabled="props.disabled"
    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 transition placeholder:text-slate-400 hover:border-slate-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100"
    @input="onInput"
  />
</template>
