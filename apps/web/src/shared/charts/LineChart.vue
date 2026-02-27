<script setup lang="ts">
import {
  CategoryScale,
  Chart,
  Filler,
  Legend,
  LineController,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
  type ChartDataset,
  type ChartOptions,
} from 'chart.js'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

type LineDataset = {
  label: string
  data: Array<number | null>
  borderColor: string
  backgroundColor?: string
  fill?: boolean
  pointRadius?: number
}

Chart.register(LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler)

const props = withDefaults(
  defineProps<{
    labels: string[]
    datasets: LineDataset[]
    height?: number
    showLegend?: boolean
    showAxes?: boolean
  }>(),
  {
    height: 290,
    showLegend: true,
    showAxes: true,
  },
)

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart<'line'> | null = null

function renderChart(): void {
  if (!canvasRef.value) {
    return
  }

  const datasets: ChartDataset<'line'>[] = props.datasets.map((dataset) => ({
    label: dataset.label,
    data: dataset.data,
    borderColor: dataset.borderColor,
    backgroundColor: dataset.backgroundColor ?? dataset.borderColor,
    fill: dataset.fill ?? false,
    tension: 0.35,
    borderWidth: 2,
    pointRadius: dataset.pointRadius ?? 2.5,
  }))

  const options: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: props.showLegend,
        position: 'bottom',
        labels: {
          usePointStyle: true,
          boxWidth: 10,
        },
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
    type: 'line',
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
  <div class="line-chart" :style="{ height: `${props.height}px` }">
    <canvas ref="canvasRef" />
  </div>
</template>

<style scoped>
.line-chart {
  width: 100%;
}
</style>
