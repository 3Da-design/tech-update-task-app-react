import type { Task } from '../types';
import { statusLabel } from './StatusLabel';

interface TaskTableProps {
  tasks: Task[];
  onEdit: (task: Task) => void;
  onDelete: (task: Task) => void;
}

export function TaskTable({ tasks, onEdit, onDelete }: TaskTableProps) {
  if (tasks.length === 0) {
    return <p className="app-panel">タスクがありません。</p>;
  }

  return (
    <table className="app-table app-panel">
      <thead>
        <tr>
          <th>タイトル</th>
          <th>ステータス</th>
          <th>期限日</th>
          <th>説明</th>
          <th aria-label="操作" />
        </tr>
      </thead>
      <tbody>
        {tasks.map((task) => (
          <tr key={task.id}>
            <td>{task.title}</td>
            <td>{statusLabel(task.status)}</td>
            <td>{task.due_date ?? '-'}</td>
            <td>{task.description ?? '-'}</td>
            <td>
              <button type="button" className="app-btn app-btn--secondary" onClick={() => onEdit(task)}>
                編集
              </button>
              <button type="button" className="app-btn app-btn--danger" onClick={() => onDelete(task)}>
                削除
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
