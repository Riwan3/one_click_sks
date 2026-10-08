const HEADER_ROWS = 2;
const LOADER_HEAD_ROWS = 2;
const FOOTER_ROWS = 1;

/** Perkiraan tinggi cluster (satuan baris): header + tiap baris loader (kepala + hauler terbanyak + footer). */
const clusterHeight = (cluster, loadersPerRow) => {
  const loaders = cluster.loaders;
  const chunks = Math.ceil(loaders.length / loadersPerRow);
  const bodyRows = Array.from({ length: chunks }, (_, index) => {
    const row = loaders.slice(index * loadersPerRow, (index + 1) * loadersPerRow);
    return LOADER_HEAD_ROWS + Math.max(1, ...row.map((loader) => (loader.units ?? loader.haulers ?? []).length)) + FOOTER_ROWS;
  });

  return HEADER_ROWS + bodyRows.reduce((sum, rows) => sum + rows, 0);
};

/**
 * Bagi cluster ke beberapa kolom (masonry): urutan ditambahkan dipertahankan, tiap cluster berikutnya
 * masuk ke kolom yang paling pendek (seri -> kolom kiri) supaya ruang kosong di bawah cluster pendek terisi.
 */
export const splitIntoColumns = (clusters, columnCount, loadersPerRow) => {
  const columns = Array.from({ length: columnCount }, () => ({ clusters: [], height: 0 }));

  clusters.forEach((cluster) => {
    const target = columns.reduce((shortest, column) => (column.height < shortest.height ? column : shortest));
    target.clusters.push(cluster);
    target.height += clusterHeight(cluster, loadersPerRow);
  });

  return columns.map((column) => column.clusters);
};

/**
 * Susun cluster berurutan menjadi segmen berisi kolom. Cluster yang loadernya tidak muat satu baris di setengah lebar
 * diberi baris penuh sendiri (5 kartu loader per baris, sisanya membungkus); cluster lain di antaranya
 * dikelompokkan masonry. Dengan satu kolom semua cluster jadi satu segmen.
 */
export const segmentClusters = (clusters, columnCount, loadersPerRow) => {
  if (columnCount < 2) return [splitIntoColumns(clusters, 1, loadersPerRow)];

  const groups = clusters.reduce((result, cluster) => {
    const isWide = cluster.loaders.length > loadersPerRow;
    const last = result[result.length - 1];

    if (!isWide && last && !last.wide) {
      last.clusters.push(cluster);
      return result;
    }

    result.push({ wide: isWide, clusters: [cluster] });
    return result;
  }, []);

  return groups.map((group) => (group.wide ? [group.clusters] : splitIntoColumns(group.clusters, columnCount, loadersPerRow)));
};
