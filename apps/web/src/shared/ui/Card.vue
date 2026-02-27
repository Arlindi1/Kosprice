<script setup lang="ts">
import { computed } from 'vue'

type CardVariant = 'default' | 'highlight' | 'subtle'
type LegacyTone = 'default' | 'brand' | 'subtle'

const props = withDefaults(
  defineProps<{
    title?: string
    subtitle?: string
    compact?: boolean
    variant?: CardVariant
    tone?: LegacyTone
  }>(),
  {
    title: '',
    subtitle: '',
    compact: false,
    variant: undefined,
    tone: 'default',
  },
)

const resolvedVariant = computed<CardVariant>(() => {
  if (props.variant) {
    return props.variant
  }

  if (props.tone === 'brand') {
    return 'highlight'
  }

  return props.tone
})
</script>

<template>
  <section
    class="overflow-hidden rounded-2xl border transition duration-200"
    :class="[
      props.compact ? 'p-4' : 'p-6 sm:p-7',
      resolvedVariant === 'default' && 'border-slate-200 bg-white/95 shadow-sm',
      resolvedVariant === 'highlight' && 'border-rose-200/70 bg-gradient-to-r from-rose-50 via-amber-50 to-sky-50 shadow-sm',
      resolvedVariant === 'subtle' && 'border-slate-200 bg-slate-50 shadow-sm',
    ]"
  >
    <header
      v-if="props.title || props.subtitle || !!$slots.header || !!$slots.actions"
      class="mb-4 flex flex-wrap items-start justify-between gap-3"
    >
      <slot name="header">
        <div class="space-y-1">
          <h2 v-if="props.title" class="text-xl font-bold tracking-tight text-slate-900">
            {{ props.title }}
          </h2>
          <p v-if="props.subtitle" class="text-sm text-slate-500">
            {{ props.subtitle }}
          </p>
        </div>
      </slot>
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </header>

    <div>
      <slot />
    </div>

    <footer v-if="$slots.footer" class="mt-4 border-t border-slate-200/70 pt-4">
      <slot name="footer" />
    </footer>
  </section>
</template>
