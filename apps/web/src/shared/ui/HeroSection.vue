<script setup lang="ts">
import { ref } from 'vue'

const props = withDefaults(
  defineProps<{
    eyebrow?: string
    title: string
    subtitle: string
    bullets: string[]
    imageSrc?: string
    imageAlt?: string
  }>(),
  {
    eyebrow: 'KosPrice',
    imageSrc: '/images/shopping-family.jpg',
    imageAlt: 'Shopping people',
  },
)

const hasImageError = ref(false)

function onImageError(): void {
  hasImageError.value = true
}
</script>

<template>
  <section class="relative overflow-hidden rounded-[2rem] border border-slate-800 bg-slate-950 px-6 py-7 md:px-8 md:py-9">
    <div class="hero-background" />

    <div class="relative grid items-start gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(340px,0.92fr)]">
      <div class="space-y-6 animate-hero-in">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-white" />
          <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-100">
            {{ props.eyebrow }}
          </span>
        </div>

        <div class="space-y-3">
          <h2 class="text-4xl font-semibold leading-[0.98] tracking-[-0.03em] text-white md:text-5xl lg:text-6xl">
            {{ props.title }}
          </h2>
          <p class="max-w-2xl text-base leading-7 text-slate-300 md:text-lg md:leading-8">
            {{ props.subtitle }}
          </p>
        </div>

        <ul class="grid gap-2.5 md:grid-cols-2">
          <li
            v-for="bullet in props.bullets"
            :key="bullet"
            class="flex items-start gap-2.5 rounded-xl border border-white/15 bg-white/5 px-3 py-2.5"
          >
            <span class="mt-[0.3rem] h-1.5 w-1.5 shrink-0 rounded-full bg-white" />
            <span class="text-[13px] font-medium leading-6 text-slate-100 md:text-[15px]">{{ bullet }}</span>
          </li>
        </ul>

        <div class="pt-1">
          <slot name="actions" />
        </div>
      </div>

      <div class="relative">
        <div
          v-if="!hasImageError && props.imageSrc"
          class="group relative overflow-hidden rounded-2xl border border-white/20 bg-slate-900/60 shadow-2xl"
        >
          <img
            :src="props.imageSrc"
            :alt="props.imageAlt"
            class="h-[320px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.03] md:h-[420px]"
            style="filter: saturate(0.72) contrast(1.03)"
            @error="onImageError"
          />
          <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent" />
        </div>

        <div
          v-else
          class="relative flex h-[320px] flex-col justify-end overflow-hidden rounded-2xl border border-white/20 bg-slate-900 p-6 md:h-[420px]"
        >
          <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.16),transparent_36%),radial-gradient(circle_at_85%_85%,rgba(148,163,184,0.25),transparent_36%)]" />
          <p class="relative text-sm font-semibold uppercase tracking-[0.14em] text-slate-300">
            KosPrice intelligence
          </p>
          <p class="relative mt-2 max-w-xs text-xl font-bold leading-snug text-white">
            Real-time market tracking for smarter everyday shopping.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(18px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-background {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(115deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.98) 100%),
    radial-gradient(circle at 0% 0%, rgba(255, 255, 255, 0.12), transparent 34%),
    radial-gradient(circle at 100% 100%, rgba(148, 163, 184, 0.18), transparent 42%);
}

.animate-hero-in {
  animation: fade-in-up 0.8s ease-out;
}
</style>
