import { useState } from 'react';
import type { FormEvent } from 'react';
import type { Task, TaskFormInput } from '../types';
import { STATUS_OPTIONS } from './StatusLabel';

const EMPTY_FORM: TaskFormInput = {
  title: '',
  description: '',
  status: 'todo',
  due_date: '',
};

function toFormInput(task: Task | null): TaskFormInput {
  if (!task) return EMPTY_FORM;
  return {
    title: task.title,
    description: task.description ?? '',
    status: task.status,
    due_date: task.due_date ?? '',
  };
}

interface TaskFormProps {
  editingTask: Task | null;
  errors: Record<string, string[]>;
  isSubmitting: boolean;
  onSubmit: (input: TaskFormInput) => void;
  onCancel: () => void;
}

export function TaskForm({ editingTask, errors, isSubmitting, onSubmit, onCancel }: TaskFormProps) {
  const [form, setForm] = useState<TaskFormInput>(() => toFormInput(editingTask));

  // 編集対象が変わったらフォームを初期化し直す
  const [lastTaskId, setLastTaskId] = useState<number | null>(editingTask?.id ?? null);
  if ((editingTask?.id ?? null) !== lastTaskId) {
    setLastTaskId(editingTask?.id ?? null);
    setForm(toFormInput(editingTask));
  }

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    onSubmit(form);
  }

  return (
    <form onSubmit={handleSubmit} className="app-panel app-form">
      <h2 className="app-page-title">{editingTask ? 'タスクを編集' : 'タスクを作成'}</h2>

      <div className="app-form-field">
        <label htmlFor="title">タイトル</label>
        <input
          id="title"
          className="app-input"
          value={form.title}
          onChange={(event) => setForm({ ...form, title: event.target.value })}
          required
          maxLength={255}
        />
        {errors.title && <p className="app-error">{errors.title.join(' ')}</p>}
      </div>

      <div className="app-form-field">
        <label htmlFor="description">説明</label>
        <textarea
          id="description"
          className="app-input"
          value={form.description}
          onChange={(event) => setForm({ ...form, description: event.target.value })}
        />
        {errors.description && <p className="app-error">{errors.description.join(' ')}</p>}
      </div>

      <div className="app-form-field">
        <label htmlFor="status">ステータス</label>
        <select
          id="status"
          className="app-input"
          value={form.status}
          onChange={(event) => setForm({ ...form, status: event.target.value as TaskFormInput['status'] })}
        >
          {STATUS_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        {errors.status && <p className="app-error">{errors.status.join(' ')}</p>}
      </div>

      <div className="app-form-field">
        <label htmlFor="due_date">期限日</label>
        <input
          id="due_date"
          type="date"
          className="app-input"
          value={form.due_date}
          onChange={(event) => setForm({ ...form, due_date: event.target.value })}
        />
        {errors.due_date && <p className="app-error">{errors.due_date.join(' ')}</p>}
      </div>

      <div className="app-form-actions">
        <button type="submit" className="app-btn app-btn--primary" disabled={isSubmitting}>
          {isSubmitting ? '保存中...' : '保存'}
        </button>
        <button type="button" className="app-btn app-btn--secondary" onClick={onCancel}>
          キャンセル
        </button>
      </div>
    </form>
  );
}
