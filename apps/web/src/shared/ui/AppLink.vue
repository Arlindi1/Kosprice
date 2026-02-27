<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { RouteLocationRaw } from 'vue-router'

const props = defineProps<{
  to?: RouteLocationRaw
  href?: string
}>()

const linkClass = computed(
  () =>
    'inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 transition hover:text-indigo-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200',
)
</script>

<template>
  <RouterLink v-if="props.to" :to="props.to" :class="linkClass">
    <slot />
  </RouterLink>
  <a
    v-else-if="props.href"
    :href="props.href"
    :class="linkClass"
  >
    <slot />
  </a>
  <span v-else :class="linkClass">
    <slot />
  </span>
</template>
