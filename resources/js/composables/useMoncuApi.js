import axios from 'axios';

const NETWORK_ERROR = 'Tidak dapat terhubung ke server.';

const messageOf = (error) => error.response?.data?.message ?? NETWORK_ERROR;

/** GET endpoint JSON One-Roster. Return `data` bila sukses; melempar Error(pesan) bila gagal. */
export async function getJson(url, params = {}) {
  try {
    const { data } = await axios.get(url, { params });
    return data.data;
  } catch (error) {
    throw new Error(messageOf(error));
  }
}

/** POST endpoint JSON One-Roster. Return `data` bila sukses; melempar Error(pesan) bila gagal. */
export async function postJson(url, payload = {}) {
  try {
    const { data } = await axios.post(url, payload);
    return data.data;
  } catch (error) {
    throw new Error(messageOf(error));
  }
}
