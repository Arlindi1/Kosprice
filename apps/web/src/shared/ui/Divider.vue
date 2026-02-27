<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    label?: string
  }>(),
  {
    label: '',
  },
)
</script>

<template>
  <div class="relative my-5">
    <div class="h-px w-full bg-slate-200" />
    <div
      v-if="props.label"
      class="absolute left-4 top-1/2 -translate-y-1/2 bg-white px-2 text-xs font-medium uppercase tracking-wide text-slate-500"
    >
      {{ props.label }}
    </div>
  </div>
</template>
