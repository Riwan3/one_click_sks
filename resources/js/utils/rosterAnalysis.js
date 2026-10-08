const average = (values) => (values.length ? Math.round((values.reduce((sum, v) => sum + v, 0) / values.length) * 10) / 10 : 0)

const emptyTargets = { tSiang: null, tMalam: null }

/** Susun blok grafik Analisis Harian: blok pertama = total semua roster, lalu satu blok per roster. */
export const buildAnalysisBlocks = (analysis) => {
  const groups = analysis?.groups ?? []
  if (!groups.length) return []

  const isMulti = groups.length > 1
  const totalChart = {
    id: 'total',
    title: isMulti ? 'Semua Roster' : `${groups[0].roster_group} — total`,
    siang: analysis.total.siang,
    malam: analysis.total.malam,
    ...emptyTargets,
  }

  const groupBlocks = groups.map((group) => {
    const charts = group.sections.map((section) => ({
      id: `r${group.roster_id}-s${section.id}`,
      title: section.name,
      siang: section.siang,
      malam: section.malam,
      tSiang: section.target_siang,
      tMalam: section.target_malam,
      rid: group.roster_id,
      sid: section.id,
      rSiang: section.roster_siang,
      rMalam: section.roster_malam,
      mSiang: section.manual_siang,
      mMalam: section.manual_malam,
    }))
    const groupTotal = { id: `r${group.roster_id}-all`, title: `Total ${group.roster_group}`, siang: group.total.siang, malam: group.total.malam, ...emptyTargets }

    return {
      title: group.roster_group,
      rosterId: group.roster_id,
      sections: group.sections.length,
      charts: group.sections.length > 1 && isMulti ? [groupTotal, ...charts] : charts,
      isFull: false,
    }
  })

  return [{ title: '', charts: [totalChart], isFull: true }, ...groupBlocks]
}

export const averageText = (chart) => {
  const targets = chart.tSiang !== null || chart.tMalam !== null
  const targetText = targets ? ` · target ${chart.tSiang ?? '-'} / ${chart.tMalam ?? '-'}` : ''
  return { siang: average(chart.siang), malam: average(chart.malam), targetText }
}

/** Warna sel jumlah orang terhadap target: merah di bawah, kuning pas, hijau di atas. */
export const targetCellClass = (value, target) => {
  if (target === null) return ''
  if (value < target) return 'bg-[#F8B4B4] text-[#7A1010] font-bold'
  return value === target ? 'bg-[#FFE58A] text-[#6B5200] font-bold' : 'bg-[#A8E0B8] text-[#0F5A2A] font-bold'
}

/** Daftar id grafik terurut, untuk mendeteksi roster/section yang bertambah atau berkurang. */
export const chartIds = (analysis) => buildAnalysisBlocks(analysis).flatMap((block) => block.charts.map((chart) => chart.id)).sort().join(',')
