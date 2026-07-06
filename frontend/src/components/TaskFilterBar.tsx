import type { FormEvent } from 'react';
import { useState } from 'react';
import type { TaskListQuery, TaskStatus } from '../types';
import { STATUS_OPTIONS } from './StatusLabel';

type StatusFilter = TaskStatus | '';
type SortFilter = 'asc' | 'desc' | '';

interface TaskFilterBarProps {
  initialQuery: TaskListQuery;
  onApply: (query: TaskListQuery) => void;
  onCreateNew: () => void;
}

export function TaskFilterBar({ initialQuery, onApply, onCreateNew }: TaskFilterBarProps) {
  const [title, setTitle] = useState(initialQuery.title ?? '');
  const [status, setStatus] = useState<StatusFilter>(initialQuery.status ?? '');
  const [dueDateSort, setDueDateSort] = useState<SortFilter>(initialQuery.due_date_sort ?? '');

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    onApply({ title, status, due_date_sort: dueDateSort });
  }

  return (
    <form onSubmit={handleSubmit} className="app-panel app-filter-bar">
      <div className="app-form-field">
        <label htmlFor="filter-title">タイトル検索</label>
        <input
          id="filter-title"
          className="app-input"
          value={title}
          onChange={(event) => setTitle(event.target.value)}
          placeholder="部分一致"
        />
      </div>

      <div className="app-form-field">
        <label htmlFor="filter-status">ステータス</label>
        <select
          id="filter-status"
          className="app-input"
          value={status}
          onChange={(event) => setStatus(event.target.value as StatusFilter)}
        >
          <option value="">すべて</option>
          {STATUS_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>

      <div className="app-form-field">
        <label htmlFor="filter-sort">期限日ソート</label>
        <select
          id="filter-sort"
          className="app-input"
          value={dueDateSort}
          onChange={(event) => setDueDateSort(event.target.value as SortFilter)}
        >
          <option value="">指定なし</option>
          <option value="asc">昇順</option>
          <option value="desc">降順</option>
        </select>
      </div>

      <div className="app-form-actions">
        <button type="submit" className="app-btn app-btn--primary">
          適用
        </button>
        <button type="button" className="app-btn app-btn--secondary" onClick={onCreateNew}>
          新規作成
        </button>
      </div>
    </form>
  );
}
