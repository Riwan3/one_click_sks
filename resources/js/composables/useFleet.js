import { useManpowerAction } from '@/composables/useManpowerV201';
import { FLEET_URL } from '@/utils/fleet';

/** Aksi Setting Fleet harian; tanggal + shift diambil dari `filters()` pada setiap panggilan. */
export function useFleetSettingActions(filters) {
  const { run, isProcessing } = useManpowerAction();
  const url = (path) => `${FLEET_URL}/settings/${path}`;
  const withDay = (data = {}) => ({ ...filters(), ...data });

  return {
    isProcessing,
    addArea: (name, onDone) => run('post', url('area'), withDay({ area_name: name }), onDone),
    updateArea: (currentName, name, onDone) => run('put', url('area'), withDay({ area_name: currentName, new_area_name: name }), onDone),
    removeArea: (name) => run('delete', url('area'), withDay({ area_name: name })),
    addCluster: (data, onDone) => run('post', url('cluster'), withDay({ cluster_name: data.name, area_name: data.area }), onDone),
    updateCluster: (currentName, data, onDone) => run('put', url('cluster'), withDay({ cluster_name: currentName, new_cluster_name: data.name, area_name: data.area }), onDone),
    removeCluster: (name) => run('delete', url('cluster'), withDay({ cluster_name: name })),
    addLoader: (data, onDone) => run('post', url('loader'), withDay(data), onDone),
    updateLoader: (data, onDone) => run('put', url('loader'), withDay(data), onDone),
    removeLoader: (loaderCode) => run('delete', url('loader'), withDay({ loader_code: loaderCode })),
    assignHauler: (loaderCode, haulerCode, onDone) => run('post', url('hauler'), withDay({ loader_code: loaderCode, hauler_code: haulerCode }), onDone),
    moveHauler: (haulerCode, loaderCode) => run('post', url('hauler/move'), withDay({ hauler_code: haulerCode, loader_code: loaderCode })),
    removeHauler: (haulerCode) => run('delete', url('hauler'), withDay({ hauler_code: haulerCode })),
    copy: (source, replace, onDone) => run('post', url('copy'), withDay({ from_date: source.date, from_shift: source.shift, replace }), onDone),
    sendBreakdown: (unitCode) => run('post', url('breakdown'), withDay({ unit_code: unitCode })),
    returnBreakdown: (unitCode) => run('delete', url('breakdown'), withDay({ unit_code: unitCode })),
    clear: () => run('delete', url('clear'), withDay()),
  };
}

/** Aksi Master Unit Fleet. */
export function useFleetMasterActions() {
  const { run, isProcessing } = useManpowerAction();
  const url = (path) => `${FLEET_URL}/master/${path}`;

  return {
    isProcessing,
    create: (data, onDone) => run('post', url('units'), data, onDone),
    update: (id, data, onDone) => run('put', url(`units/${id}`), data, onDone),
    setActive: (id, isActive) => run('put', url(`units/${id}/active`), { is_active: isActive }),
    remove: (id) => run('delete', url(`units/${id}`)),
    pull: (categories, onDone) => run('post', url('pull'), { categories }, onDone),
  };
}
