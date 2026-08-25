import type { TaskStatus } from '../types';

export const STATUS_OPTIONS: { value: TaskStatus; label: string }[] = [
  { value: 0, label: '未着手' },
  { value: 1, label: '進行中' },
  { value: 2, label: '完了' },
];

export function statusLabel(status: TaskStatus): string {
  return STATUS_OPTIONS.find((option) => option.value === status)?.label ?? String(status);
}
