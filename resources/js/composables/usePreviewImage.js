import { ref } from 'vue';
import html2canvas from 'html2canvas-pro';
import { useManpowerNotice } from '@/composables/useManpowerV201';

/** Ubah elemen preview jadi PNG: unduh atau salin ke clipboard (html2canvas, bundel lokal). */
export function usePreviewImage(sheetRef, filename) {
  const { notify } = useManpowerNotice();
  const busyAction = ref('');

  const render = () => html2canvas(sheetRef.value, {
    scale: 2,
    useCORS: true,
    backgroundColor: '#ffffff',
    logging: false,
    scrollX: 0,
    scrollY: -window.scrollY,
    width: sheetRef.value.scrollWidth,
    windowWidth: sheetRef.value.scrollWidth + 50,
  });

  const toBlob = (canvas) => new Promise((resolve, reject) => {
    canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Gambar kosong.'))), 'image/png');
  });

  const withBusy = async (action, task, failMessage) => {
    busyAction.value = action;
    try {
      await task();
    } catch (error) {
      notify('error', `${failMessage}: ${error.message}`);
    } finally {
      busyAction.value = '';
    }
  };

  const download = () => withBusy('download', async () => {
    const link = document.createElement('a');
    link.download = filename();
    link.href = (await render()).toDataURL('image/png');
    link.click();
  }, 'Gagal membuat gambar');

  const copy = () => {
    const isSupported = navigator.clipboard && window.ClipboardItem;
    if (!isSupported) return notify('error', 'Browser tidak mendukung salin gambar. Pakai Unduh PNG.');

    return withBusy('copy', async () => {
      const blob = await toBlob(await render());
      await navigator.clipboard.write([new window.ClipboardItem({ 'image/png': blob })]);
      notify('success', 'Gambar disalin. Tempel (Ctrl+V) di WhatsApp/aplikasi lain.');
    }, 'Gagal menyalin gambar');
  };

  return { busyAction, download, copy };
}
