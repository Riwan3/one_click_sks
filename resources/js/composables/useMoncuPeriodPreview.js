import { reactive, ref, watch } from 'vue';
import { getJson } from '@/composables/useMoncuApi';

const PREVIEW_URL = '/one-roster/moncu/periods/preview';
const DEBOUNCE_MS = 250;
const emptyResult = () => ({ akhir: '', induksi: '', plan: '' });

/** Pratinjau AKHIR/INDUKSI/PLAN (dihitung server, tanpa menyimpan) mengikuti isi form periode. */
export function useMoncuPeriodPreview(form) {
  const result = reactive(emptyResult());
  const warnings = ref('');
  const error = ref('');
  let timer = null;
  let sequence = 0;

  const clear = () => {
    Object.assign(result, emptyResult());
    warnings.value = '';
    error.value = '';
  };

  const run = async () => {
    const mine = ++sequence;
    if (!form.awal) return clear();
    try {
      const data = await getJson(PREVIEW_URL, {
        nrp: form.nrp, year: form.year, period_no: form.period_no,
        awal: form.awal, tahunan: form.tahunan ?? '', izin_resmi: form.izin_resmi ?? '',
      });
      if (mine !== sequence) return;
      error.value = '';
      Object.assign(result, { akhir: data.akhir ?? '', induksi: data.induksi ?? '', plan: data.plan ?? '' });
      warnings.value = (data.warnings ?? []).join(' ');
    } catch (e) {
      if (mine !== sequence) return;
      Object.assign(result, emptyResult());
      warnings.value = '';
      error.value = e.message;
    }
  };

  const schedule = () => {
    clearTimeout(timer);
    timer = setTimeout(run, DEBOUNCE_MS);
  };

  watch(() => [form.awal, form.tahunan, form.izin_resmi], schedule);

  return { result, warnings, error, refresh: run };
}
