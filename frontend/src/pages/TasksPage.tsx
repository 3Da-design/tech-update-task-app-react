import { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';
import { createTask, deleteTask, listTasks, updateTask } from '../api/tasks';
import { TaskFilterBar } from '../components/TaskFilterBar';
import { TaskForm } from '../components/TaskForm';
import { TaskTable } from '../components/TaskTable';
import type { Task, TaskFormInput, TaskListQuery } from '../types';

export function TasksPage() {
  const { user, logout } = useAuth();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [query, setQuery] = useState<TaskListQuery>({});
  const [isLoading, setIsLoading] = useState(true);
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingTask, setEditingTask] = useState<Task | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [loadError, setLoadError] = useState<string | null>(null);

  const reload = useCallback((currentQuery: TaskListQuery) => {
    listTasks(currentQuery)
      .then((data) => {
        setTasks(data);
        setLoadError(null);
      })
      .catch(() => setLoadError('タスクの取得に失敗しました。'))
      .finally(() => setIsLoading(false));
  }, []);

  useEffect(() => {
    reload(query);
  }, [query, reload]);

  function handleApplyFilter(nextQuery: TaskListQuery) {
    setQuery(nextQuery);
  }

  function handleCreateNew() {
    setEditingTask(null);
    setErrors({});
    setIsFormOpen(true);
  }

  function handleEdit(task: Task) {
    setEditingTask(task);
    setErrors({});
    setIsFormOpen(true);
  }

  function handleCancelForm() {
    setIsFormOpen(false);
    setEditingTask(null);
    setErrors({});
  }

  async function handleSubmitForm(input: TaskFormInput) {
    setIsSubmitting(true);
    setErrors({});

    const payload = {
      title: input.title,
      description: input.description === '' ? null : input.description,
      status: input.status,
      due_date: input.due_date === '' ? null : input.due_date,
    };

    try {
      if (editingTask) {
        await updateTask(editingTask.id, payload);
      } else {
        await createTask(payload);
      }
      setIsFormOpen(false);
      setEditingTask(null);
      reload(query);
    } catch (err) {
      if (axios.isAxiosError(err) && err.response?.status === 422) {
        setErrors(err.response.data.errors ?? {});
      } else {
        setLoadError('保存に失敗しました。');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDelete(task: Task) {
    if (!window.confirm(`「${task.title}」を削除しますか？`)) {
      return;
    }
    await deleteTask(task.id);
    reload(query);
  }

  return (
    <div className="app-main">
      <header className="app-page-heading">
        <h1 className="app-page-title">タスク一覧</h1>
        <div>
          <span className="app-header__user">{user?.name}</span>
          <button type="button" className="app-btn app-btn--secondary" onClick={() => logout()}>
            ログアウト
          </button>
        </div>
      </header>

      <TaskFilterBar initialQuery={query} onApply={handleApplyFilter} onCreateNew={handleCreateNew} />

      {isFormOpen && (
        <TaskForm
          editingTask={editingTask}
          errors={errors}
          isSubmitting={isSubmitting}
          onSubmit={handleSubmitForm}
          onCancel={handleCancelForm}
        />
      )}

      {loadError && <p className="app-error">{loadError}</p>}
      {isLoading ? <p className="app-loading">読み込み中...</p> : <TaskTable tasks={tasks} onEdit={handleEdit} onDelete={handleDelete} />}
    </div>
  );
}
