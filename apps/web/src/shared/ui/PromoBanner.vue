<script setup lang="ts">
import { computed } from 'vue'

type PromoBannerVariant = 'amber' | 'sky' | 'purple' | 'emerald'

const props = withDefaults(
  defineProps<{
    title: string
    subtitle?: string
    variant?: PromoBannerVariant
  }>(),
  {
    subtitle: '',
    variant: 'amber',
  },
)

const bannerConfig = computed(() => {
  const configs = {
    sky: {
      border: 'border-slate-300',
      badge: 'bg-slate-900 text-white',
      title: 'text-slate-900',
    },
    amber: {
      border: 'border-slate-300',
      badge: 'bg-slate-900 text-white',
      title: 'text-slate-900',
    },
    purple: {
      border: 'border-slate-300',
      badge: 'bg-slate-900 text-white',
      title: 'text-slate-900',
    },
    emerald: {
      border: 'border-slate-300',
      badge: 'bg-slate-900 text-white',
      title: 'text-slate-900',
    },
  }

  return configs[props.variant]
})
</script>

<template>
  <section
    class="group relative overflow-hidden rounded-3xl border bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
    :class="[bannerConfig.border]"
  >
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_0%_0%,rgba(15,23,42,0.08),transparent_34%)] opacity-70" />

    <div class="relative px-6 py-6 md:px-7 md:py-7">
      <div class="grid items-center gap-5 md:grid-cols-[minmax(0,1.2fr)_minmax(220px,0.8fr)]">
        <div class="space-y-3">
          <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.14em]" :class="bannerConfig.badge">
            Featured
          </div>

          <h3
            class="text-2xl font-bold leading-tight tracking-tight md:text-3xl"
            :class="bannerConfig.title"
          >
            {{ props.title }}
          </h3>

          <p v-if="props.subtitle" class="text-sm font-medium leading-relaxed text-slate-600 md:text-base">
            {{ props.subtitle }}
          </p>

          <div class="pt-1">
            <slot />
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
          <slot name="illustration">
            <div class="flex h-full min-h-[130px] items-center justify-center rounded-xl border border-slate-200 bg-white">
              <span class="text-sm font-semibold text-slate-500">No preview</span>
            </div>
          </slot>
        </div>
      </div>
    </div>
  </section>
</template>
