import { apiClient } from './client';
import type { Task, TaskListQuery } from '../types';

export async function listTasks(query: TaskListQuery): Promise<Task[]> {
  const params: Record<string, string> = {};
  if (query.title) params.title = query.title;
  if (query.status) params.status = query.status;
  if (query.priority) params.priority = query.priority;
  if (query.priority_sort) params.priority_sort = query.priority_sort;
  if (query.due_date_sort) params.due_date_sort = query.due_date_sort;

  const response = await apiClient.get<{ data: Task[] }>('/api/tasks', { params });
  return response.data.data;
}

export interface TaskPayload {
  title: string;
  description: string | null;
  status: Task['status'];
  priority: Task['priority'];
  due_date: string | null;
}

export async function createTask(payload: TaskPayload): Promise<Task> {
  const response = await apiClient.post<{ data: Task }>('/api/tasks', payload);
  return response.data.data;
}

export async function updateTask(id: number, payload: TaskPayload): Promise<Task> {
  const response = await apiClient.put<{ data: Task }>(`/api/tasks/${id}`, payload);
  return response.data.data;
}

export async function deleteTask(id: number): Promise<void> {
  await apiClient.delete(`/api/tasks/${id}`);
}
