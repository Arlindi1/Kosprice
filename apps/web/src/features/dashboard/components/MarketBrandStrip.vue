<script setup lang="ts">
import { getMarketLogo } from '@/features/markets/utils/getMarketLogo'

const brands = [
  { key: 'maxi', label: 'Maxi' },
  { key: 'meridian', label: 'Meridian' },
  { key: 'interex', label: 'Interex' },
  { key: 'vivafresh', label: 'Viva Fresh' },
]
</script>

<template>
  <section class="space-y-3">
    <h3 class="text-heading">Top brands near you</h3>

    <div class="overflow-x-auto pb-1">
      <ol class="flex min-w-max items-center gap-3">
        <li
          v-for="brand in brands"
          :key="brand.key"
          class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm"
        >
          <img
            :src="getMarketLogo(brand.key)"
            :alt="`${brand.label} logo`"
            class="h-8 w-8 rounded-md border border-slate-200 bg-white p-0.5 object-contain"
          />
          <span class="text-sm font-semibold text-slate-700">{{ brand.label }}</span>
        </li>
      </ol>
    </div>
  </section>
</template>
