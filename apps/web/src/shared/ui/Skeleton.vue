<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    width?: string
    height?: string
    rounded?: string
  }>(),
  {
    width: '100%',
    height: '1rem',
    rounded: '0.75rem',
  },
)
</script>

<template>
  <div
    class="animate-pulse bg-gradient-to-r from-slate-200 via-slate-100 to-slate-200"
    :style="{ width: props.width, height: props.height, borderRadius: props.rounded }"
  />
</template>

