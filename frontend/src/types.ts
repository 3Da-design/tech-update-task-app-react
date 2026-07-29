export type TaskStatus = 0 | 1 | 2;

export interface Task {
  id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  due_date: string | null;
}

export interface TaskFormInput {
  title: string;
  description: string;
  status: TaskStatus;
  due_date: string;
}

export interface TaskListQuery {
  title?: string;
  status?: TaskStatus | '';
  due_date_sort?: 'asc' | 'desc' | '';
}

export interface User {
  id: number;
  name: string;
  email: string;
}
