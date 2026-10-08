import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const SIANG = '#F59E0B'
const MALAM = '#4F46E5'
const MIN_POINTS = 4

const line = (label, data, color, isDashed) => ({
  label,
  data: [...data],
  borderColor: color,
  backgroundColor: color,
  borderWidth: isDashed ? 1.5 : 2.5,
  borderDash: isDashed ? [6, 5] : [],
  pointRadius: isDashed ? 0 : 3,
  pointHoverRadius: isDashed ? 0 : 5,
  tension: 0.25,
  fill: false,
})

const buildDatasets = ({ chart, labels }) => {
  const flat = (value) => labels.map(() => value)
  return [
    line('Siang', chart.siang, SIANG, false),
    line('Malam', chart.malam, MALAM, false),
    ...(chart.tSiang === null ? [] : [line('Target Siang', flat(chart.tSiang), SIANG, true)]),
    ...(chart.tMalam === null ? [] : [line('Target Malam', flat(chart.tMalam), MALAM, true)]),
  ]
}

/**
 * Grafik garis Siang/Malam dengan zoom (seret, Ctrl + roda, tombol), geser, rapatkan sumbu Y, dan reset.
 * getSource: () => ({ chart, labels }) — dipanggil ulang setiap refresh().
 */
export function useAnalysisChart(canvasRef, getSource) {
  const isFit = ref(false)
  const isFull = ref(true)
  let instance = null
  let lo = 0
  let hi = 0
  let drag = null

  const pointCount = () => getSource().labels.length

  const selectionPlugin = {
    id: 'analysisSelection',
    afterDraw: (chart) => {
      if (!drag || drag.x0 === drag.x1) return
      const area = chart.chartArea
      const x0 = Math.max(area.left, Math.min(drag.x0, drag.x1))
      const x1 = Math.min(area.right, Math.max(drag.x0, drag.x1))
      const { ctx } = chart
      ctx.save()
      ctx.fillStyle = 'rgba(59,130,246,.15)'
      ctx.strokeStyle = 'rgba(59,130,246,.8)'
      ctx.fillRect(x0, area.top, x1 - x0, area.bottom - area.top)
      ctx.strokeRect(x0, area.top, x1 - x0, area.bottom - area.top)
      ctx.restore()
    },
  }

  const fitYAxis = (y) => {
    const visible = instance.data.datasets.flatMap((d) => d.data.slice(lo, hi + 1)).filter((v) => v !== null && v !== undefined)
    const min = visible.length ? Math.min(...visible) : 0
    const max = visible.length ? Math.max(...visible) : 1
    const padding = Math.max(1, Math.ceil((max - min) * 0.15))
    y.beginAtZero = false
    y.min = Math.max(0, min - padding)
    y.max = max + padding
  }

  const apply = () => {
    const { x, y } = instance.options.scales
    const full = lo === 0 && hi === pointCount() - 1
    x.min = lo
    x.max = hi
    if (full && !isFit.value) {
      y.min = undefined
      y.max = undefined
      y.beginAtZero = true
    } else {
      fitYAxis(y)
    }
    isFull.value = full
    instance.update('none')
  }

  const setRange = (from, to) => {
    const span = Math.max(MIN_POINTS - 1, to - from)
    lo = Math.max(0, Math.min(from, pointCount() - 1 - span))
    hi = Math.min(pointCount() - 1, lo + span)
    apply()
  }

  const zoom = (factor, centerIndex) => {
    const span = hi - lo
    const newSpan = Math.round(span * factor)
    if (newSpan >= pointCount() - 1) return setRange(0, pointCount() - 1)
    const size = Math.max(MIN_POINTS - 1, newSpan)
    const center = centerIndex ?? (lo + hi) / 2
    const ratio = span ? (center - lo) / span : 0.5
    const start = Math.round(center - size * ratio)
    return setRange(start, start + size)
  }

  const pixelX = (event) => event.clientX - canvasRef.value.getBoundingClientRect().left
  const indexAt = (x) => Math.max(0, Math.min(pointCount() - 1, Math.round(instance.scales.x.getValueForPixel(x))))

  const onMouseDown = (event) => {
    const x = pixelX(event)
    if (x < instance.chartArea.left || x > instance.chartArea.right) return
    drag = { x0: x, x1: x }
  }

  const onMouseMove = (event) => {
    if (!drag) return
    drag.x1 = pixelX(event)
    instance.draw()
  }

  const onMouseUp = () => {
    if (!drag) return
    const finished = drag
    drag = null
    if (Math.abs(finished.x1 - finished.x0) <= 8) return instance.draw()
    return setRange(indexAt(Math.min(finished.x0, finished.x1)), indexAt(Math.max(finished.x0, finished.x1)))
  }

  const reset = () => {
    isFit.value = false
    setRange(0, pointCount() - 1)
  }

  const onWheel = (event) => {
    if (!event.ctrlKey) return
    event.preventDefault()
    zoom(event.deltaY < 0 ? 0.7 : 1.4, instance.scales.x.getValueForPixel(pixelX(event)))
  }

  const refresh = () => {
    if (!instance) return
    const source = getSource()
    instance.data.labels = source.labels
    instance.data.datasets = buildDatasets(source)
    apply()
  }

  const pan = (direction) => {
    const step = Math.max(1, Math.round((hi - lo + 1) / 2)) * direction
    setRange(lo + step, hi + step)
  }

  const toggleFit = () => {
    isFit.value = !isFit.value
    apply()
  }

  onMounted(() => {
    const source = getSource()
    hi = source.labels.length - 1
    instance = new Chart(canvasRef.value, {
      type: 'line',
      data: { labels: source.labels, datasets: buildDatasets(source) },
      plugins: [selectionPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } } },
        scales: {
          x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 16 } },
          y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Orang' } },
        },
      },
    })
    apply()
    canvasRef.value.addEventListener('mousedown', onMouseDown)
    canvasRef.value.addEventListener('dblclick', reset)
    canvasRef.value.addEventListener('wheel', onWheel, { passive: false })
    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseup', onMouseUp)
  })

  onBeforeUnmount(() => {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseup', onMouseUp)
    instance?.destroy()
  })

  return {
    isFit,
    isFull,
    refresh,
    reset,
    toggleFit,
    pan,
    zoomIn: () => zoom(0.5),
    zoomOut: () => zoom(2),
  }
}
