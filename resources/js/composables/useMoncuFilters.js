import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

/** Form filter halaman MONCU berbasis query string (GET Inertia), dengan tombol Terapkan & Reset. */
export function useMoncuFilters(url, initial, extra = () => ({})) {
  const filters = reactive({ ...initial });

  const apply = () => {
    const query = Object.fromEntries(Object.entries({ ...filters, ...extra() }).filter(([, v]) => v !== '' && v !== null && v !== undefined));
    router.get(url, query, { preserveScroll: true });
  };

  const reset = () => router.get(url);

  return { filters, apply, reset };
}
