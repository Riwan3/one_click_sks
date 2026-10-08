import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { BASE_URL } from '@/utils/manpowerV201';

const NOTICE_DURATION = { success: 3000, error: 7000 };

const notice = reactive({ tone: 'success', message: '' });
let noticeTimer = null;

/** Notifikasi singkat (toast) yang dipakai bersama semua halaman Setting Manpower v2.01. */
export function useManpowerNotice() {
  const notify = (tone, message) => {
    if (!message) return;
    clearTimeout(noticeTimer);
    Object.assign(notice, { tone, message });
    noticeTimer = setTimeout(() => { notice.message = ''; }, NOTICE_DURATION[tone]);
  };

  return { notice, notify };
}

const SEND = {
  post: (url, data, options) => router.post(url, data, options),
  put: (url, data, options) => router.put(url, data, options),
  delete: (url, data, options) => router.delete(url, { ...options, data }),
};

/** Kirim aksi via Inertia (halaman dimuat ulang, scroll dipertahankan); pesan sukses/gagal tampil sebagai toast. */
export function useManpowerAction() {
  const { notify } = useManpowerNotice();
  const isProcessing = ref(false);

  const run = (method, url, data = {}, onDone = null) => {
    SEND[method](url, data, {
      preserveScroll: true,
      preserveState: true,
      onStart: () => { isProcessing.value = true; },
      onFinish: () => { isProcessing.value = false; },
      onSuccess: (page) => {
        notify('success', page.props.flash?.success);
        onDone?.();
      },
      onError: (errors) => notify('error', errors.form ?? Object.values(errors)[0] ?? 'Gagal.'),
    });
  };

  return { run, isProcessing };
}

/** Aksi Setting Harian untuk satu setting (endpoint = port api/manpower-v201/setting.php). */
export function useHarianActions(settingId) {
  const { run, isProcessing } = useManpowerAction();
  const url = (path = '') => `${BASE_URL}/settings/${settingId()}${path}`;

  return {
    isProcessing,
    sync: () => run('post', url('/sync')),
    finalize: () => run('post', url('/finalize')),
    reopen: () => run('post', url('/reopen')),
    assign: (unitId, nrp) => run('post', url(`/units/${unitId}/assign`), { nrp }),
    unassign: (unitId) => run('delete', url(`/units/${unitId}/assign`)),
    resolveDouble: (unitId, nrp) => run('post', url(`/units/${unitId}/double`), { nrp }),
    addLain: (nrp, label, onDone) => run('post', url('/lain'), { nrp, label }, onDone),
    removeLain: (nrp) => run('delete', url('/lain'), { nrp }),
    create: (date, shift) => run('post', `${BASE_URL}/settings`, { date, shift }),
  };
}

/** Aksi Master CSA (endpoint = port api/manpower-v201/master.php). */
export function useMasterActions() {
  const { run, isProcessing } = useManpowerAction();
  const url = (path) => `${BASE_URL}/master/${path}`;

  return {
    isProcessing,
    addCsa: (name, onDone) => run('post', url('csa'), { name }, onDone),
    renameCsa: (id, name, onDone) => run('put', url(`csa/${id}`), { name }, onDone),
    lockCsa: (id, locked) => run('put', url(`csa/${id}/lock`), { locked }),
    moveCsa: (id, dir) => run('post', url(`csa/${id}/move`), { dir }),
    deleteCsa: (id) => run('delete', url(`csa/${id}`)),
    addCategory: (csaId, name, colPos, onDone) => run('post', url('categories'), { csa_id: csaId, name, col_pos: colPos }, onDone),
    updateCategory: (id, name, colPos, onDone) => run('put', url(`categories/${id}`), { name, col_pos: colPos }, onDone),
    moveCategory: (id, dir) => run('post', url(`categories/${id}/move`), { dir }),
    deleteCategory: (id) => run('delete', url(`categories/${id}`)),
    addUnits: (categoryId, units, highRisk, onDone) => run('post', url('units'), { category_id: categoryId, units, high_risk: highRisk }, onDone),
    renameUnit: (id, unit, onDone) => run('put', url(`units/${id}`), { unit }, onDone),
    setUnitRisk: (id, highRisk) => run('put', url(`units/${id}/risk`), { high_risk: highRisk }),
    moveUnit: (id, dir) => run('post', url(`units/${id}/move`), { dir }),
    moveUnitToCategory: (id, categoryId) => run('put', url(`units/${id}/category`), { category_id: categoryId }),
    deleteUnit: (id) => run('delete', url(`units/${id}`)),
  };
}
