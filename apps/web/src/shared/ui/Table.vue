<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    dense?: boolean
  }>(),
  {
    dense: false,
  },
)
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full border-collapse" :class="{ dense: props.dense }">
      <thead>
        <tr class="border-b border-slate-200">
          <slot name="head" />
        </tr>
      </thead>
      <tbody class="text-sm">
        <slot />
      </tbody>
    </table>
  </div>
</template>

<style scoped>
table :deep(th) {
  text-align: left;
  padding: 0.65rem 0.6rem;
  font-size: 0.75rem;
  letter-spacing: 0.02em;
  color: #64748b;
  font-weight: 600;
}

table :deep(td) {
  padding: 0.75rem 0.6rem;
  border-bottom: 1px solid #edf2fa;
  color: #1f2937;
  vertical-align: top;
}

table.dense :deep(td) {
  padding: 0.55rem 0.6rem;
}
</style>
