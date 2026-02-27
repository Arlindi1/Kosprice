<script setup lang="ts">
import {
  BarController,
  BarElement,
  CategoryScale,
  Chart,
  Legend,
  LinearScale,
  Tooltip,
  type ChartDataset,
  type ChartOptions,
} from 'chart.js'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

type BarDataset = {
  label: string
  data: Array<number | null>
  backgroundColor: string | string[]
  borderColor?: string | string[]
}

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = withDefaults(
  defineProps<{
    labels: string[]
    datasets: BarDataset[]
    height?: number
    showLegend?: boolean
    showAxes?: boolean
  }>(),
  {
    height: 180,
    showLegend: false,
    showAxes: true,
  },
)

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart<'bar'> | null = null

function renderChart(): void {
  if (!canvasRef.value) {
    return
  }

  const datasets: ChartDataset<'bar'>[] = props.datasets.map((dataset) => ({
    label: dataset.label,
    data: dataset.data,
    backgroundColor: dataset.backgroundColor,
    borderColor: dataset.borderColor ?? dataset.backgroundColor,
    borderWidth: 1,
    borderRadius: 8,
    borderSkipped: false,
    maxBarThickness: 42,
  }))

  const options: ChartOptions<'bar'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: props.showLegend,
      },
      tooltip: {
        mode: 'index',
        intersect: false,
      },
    },
    scales: {
      x: {
        display: props.showAxes,
        grid: {
          display: false,
        },
      },
      y: {
        display: props.showAxes,
        beginAtZero: false,
        grid: {
          color: '#e7eef9',
        },
      },
    },
  }

  if (chartInstance) {
    chartInstance.data.labels = props.labels
    chartInstance.data.datasets = datasets
    chartInstance.options = options
    chartInstance.update()
    return
  }

  chartInstance = new Chart(canvasRef.value, {
    type: 'bar',
    data: {
      labels: props.labels,
      datasets,
    },
    options,
  })
}

onMounted(renderChart)

watch(
  () => [props.labels, props.datasets, props.showAxes, props.showLegend],
  () => {
    renderChart()
  },
  { deep: true },
)

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
})
</script>

<template>
  <div class="bar-chart" :style="{ height: `${props.height}px` }">
    <canvas ref="canvasRef" />
  </div>
</template>

<style scoped>
.bar-chart {
  width: 100%;
}
</style>
