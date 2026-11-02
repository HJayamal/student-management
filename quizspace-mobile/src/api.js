import axios from 'axios';

export function normalizeApiBaseUrl(value) {
  const clean = String(value || '').trim().replace(/\/+$/, '');
  if (!clean) throw new Error('Enter the Laravel API address.');
  if (!/^https?:\/\//i.test(clean)) {
    throw new Error('The API address must start with http:// or https://.');
  }
  return /\/api$/i.test(clean) ? clean : `${clean}/api`;
}

export function createApi(baseUrl, token = null) {
  return axios.create({
    baseURL: normalizeApiBaseUrl(baseUrl),
    timeout: 20000,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });
}

export function getApiError(error) {
  const responseMessage = error?.response?.data?.message;
  if (responseMessage) return responseMessage;
  if (error?.code === 'ECONNABORTED') return 'The server took too long to respond. Try again.';
  if (error?.message === 'Network Error') {
    return 'Cannot reach Laravel. Check the API URL, Wi-Fi, firewall, and that php artisan serve uses --host=0.0.0.0.';
  }
  return error?.message || 'Something went wrong. Please try again.';
}
