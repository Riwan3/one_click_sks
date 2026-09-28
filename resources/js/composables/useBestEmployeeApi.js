/**
 * Helper GET JSON kecil untuk endpoint read-only Best Employee (search kandidat,
 * detail attempt) - bukan untuk create/update/delete, yang tetap lewat Inertia.
 */
export async function beGetJson(url, params = {}) {
    const query = new URLSearchParams(
        Object.fromEntries(Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== ''))
    ).toString();
    const response = await fetch(query ? `${url}?${query}` : url, {
        headers: { Accept: 'application/json' },
    });
    if (! response.ok) {
        throw new Error('Gagal memuat data.');
    }
    return response.json();
}
