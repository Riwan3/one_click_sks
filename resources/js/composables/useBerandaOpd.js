import { computed, ref } from 'vue'

const NO_POSITION = '(tanpa posisi)'
const SEVERITY_ORDER = { under: 0, high: 1, ok: 2, na: 3 }
const ASCENDING_FIRST = ['name', 'position', 'status']

const FILTERS = {
  all: () => true,
  under: (r) => r.status === 'under',
  bad: (r) => r.status === 'under' || r.status === 'high',
  tiga: (r) => r.isOverMinYears,
  na: (r) => r.status === 'na',
}

const SORT_VALUE = {
  name: (r) => r.name.toLowerCase(),
  position: (r) => r.position.toLowerCase(),
  rank: (r) => r.gradeRank,
  status: (r) => SEVERITY_ORDER[r.status],
  yearsInGrade: (r) => r.yearsInGrade ?? -1,
  yearsInPosition: (r) => r.yearsInPosition ?? -1,
}

const positionOf = (row) => row.position || NO_POSITION

/** Saringan, pencarian, pengurutan, dan ringkasan untuk tabel Beranda OPD. */
export function useBerandaOpd(rows) {
  const filter = ref('all')
  const position = ref('')
  const search = ref('')
  const sortKey = ref(null)
  const sortDir = ref(1)

  const counts = computed(() => ({
    total: rows.value.length,
    under: rows.value.filter(FILTERS.under).length,
    bad: rows.value.filter(FILTERS.bad).length,
    tiga: rows.value.filter(FILTERS.tiga).length,
    na: rows.value.filter(FILTERS.na).length,
    high: rows.value.filter((r) => r.status === 'high').length,
    ok: rows.value.filter((r) => r.status === 'ok').length,
  }))

  const positions = computed(() => {
    const byPosition = new Map()
    rows.value.forEach((r) => {
      const entry = byPosition.get(positionOf(r)) ?? { name: positionOf(r), total: 0, under: 0, high: 0 }
      entry.total++
      if (r.status === 'under') entry.under++
      if (r.status === 'high') entry.high++
      byPosition.set(entry.name, entry)
    })
    return [...byPosition.values()].sort((a, b) => b.under - a.under || b.total - a.total)
  })

  const visibleRows = computed(() => {
    const term = search.value.trim().toLowerCase()
    const matched = rows.value.filter((r) => FILTERS[filter.value](r)
      && (position.value === '' || positionOf(r) === position.value)
      && (term === '' || `${r.nrp} ${r.name} ${r.position}`.toLowerCase().includes(term)))

    if (!sortKey.value) return matched
    const valueOf = SORT_VALUE[sortKey.value]
    return [...matched].sort((a, b) => {
      const [x, y] = [valueOf(a), valueOf(b)]
      return (Number(x > y) - Number(x < y)) * sortDir.value
    })
  })

  const setSort = (key) => {
    sortDir.value = sortKey.value === key ? -sortDir.value : (ASCENDING_FIRST.includes(key) ? 1 : -1)
    sortKey.value = key
  }

  return { filter, position, search, sortKey, counts, positions, visibleRows, setSort }
}
