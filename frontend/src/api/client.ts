import axios from 'axios';

// バックエンド（Laravel API）のオリジン。開発時は Vite(5175) と別オリジンの 8004。
export const API_BASE_URL =
  (import.meta.env.VITE_API_BASE_URL as string | undefined) ?? 'http://localhost:8004';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

// Sanctum SPA 認証: セッション Cookie + XSRF-TOKEN Cookie を取得する。
// ログイン前・アプリ起動時に一度呼ぶ。
export async function ensureCsrfCookie(): Promise<void> {
  await axios.get(`${API_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });
}
