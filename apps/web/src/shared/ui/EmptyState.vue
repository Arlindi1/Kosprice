<script setup lang="ts">
import Button from '@/shared/ui/Button.vue'

const props = withDefaults(
  defineProps<{
    title: string
    message: string
    ctaLabel?: string
  }>(),
  {
    ctaLabel: '',
  },
)

const emit = defineEmits<{
  retry: []
}>()
</script>

<template>
  <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-6 py-10 text-center">
    <div class="mb-3 grid h-14 w-14 place-items-center rounded-full bg-white text-brand-600 shadow-sm">
      <slot name="icon">
        <svg class="h-8 w-8" viewBox="0 0 64 64" fill="none" aria-hidden="true">
          <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="2.2" opacity="0.3" />
          <circle cx="24" cy="28" r="2.2" fill="currentColor" opacity="0.7" />
          <circle cx="40" cy="28" r="2.2" fill="currentColor" opacity="0.7" />
          <path
            d="M22 40c2.5-3.2 6.1-4.8 10-4.8s7.5 1.6 10 4.8"
            stroke="currentColor"
            stroke-width="2.4"
            stroke-linecap="round"
            opacity="0.7"
          />
        </svg>
      </slot>
    </div>
    <h3 class="text-base font-semibold text-slate-900">{{ props.title }}</h3>
    <p class="mt-1 max-w-md text-sm text-slate-500">{{ props.message }}</p>
    <Button v-if="props.ctaLabel" class="mt-4" variant="secondary" size="sm" @click="emit('retry')">
      {{ props.ctaLabel }}
    </Button>
  </div>
</template>
