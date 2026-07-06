import { apiClient, ensureCsrfCookie } from './client';
import type { User } from '../types';

export async function fetchCurrentUser(): Promise<User | null> {
  try {
    const response = await apiClient.get<User>('/api/user');
    return response.data;
  } catch {
    return null;
  }
}

export async function login(email: string, password: string): Promise<User> {
  await ensureCsrfCookie();
  const response = await apiClient.post<{ data: User }>('/api/login', { email, password });
  return response.data.data;
}

export async function register(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string,
): Promise<User> {
  await ensureCsrfCookie();
  const response = await apiClient.post<{ data: User }>('/api/register', {
    name,
    email,
    password,
    password_confirmation: passwordConfirmation,
  });
  return response.data.data;
}

export async function logout(): Promise<void> {
  await apiClient.post('/api/logout');
}
