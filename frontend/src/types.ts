export type TaskStatus = 'todo' | 'in_progress' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high';

export interface Task {
  id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  due_date: string | null;
}

export interface TaskFormInput {
  title: string;
  description: string;
  status: TaskStatus;
  priority: TaskPriority;
  due_date: string;
}

export interface TaskListQuery {
  title?: string;
  status?: TaskStatus | '';
  priority?: TaskPriority | '';
  priority_sort?: 'asc' | 'desc' | '';
  due_date_sort?: 'asc' | 'desc' | '';
}

export interface User {
  id: number;
  name: string;
  email: string;
}
