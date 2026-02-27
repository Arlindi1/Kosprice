<script setup lang="ts">
import { computed } from 'vue'

type BadgeVariant = 'neutral' | 'accent' | 'brand' | 'success' | 'warn'

const props = withDefaults(
  defineProps<{
    variant?: BadgeVariant
  }>(),
  {
    variant: 'neutral',
  },
)

const className = computed(() => {
  const byVariant: Record<BadgeVariant, string> = {
    neutral: 'border-slate-200 bg-slate-100 text-slate-700',
    accent: 'border-rose-200 bg-rose-100 text-rose-700',
    brand: 'border-indigo-200 bg-indigo-100 text-indigo-700',
    success: 'border-emerald-200 bg-emerald-100 text-emerald-700',
    warn: 'border-amber-200 bg-amber-100 text-amber-700',
  }

  return [
    'inline-flex h-6 items-center rounded-full border px-2.5 text-[11px] font-semibold uppercase tracking-wide',
    byVariant[props.variant],
  ]
})
</script>

<template>
  <span :class="className">
    <slot />
  </span>
</template>
