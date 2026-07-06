import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';

export function RegisterPage() {
  const { user, isLoading, register } = useAuth();
  const navigate = useNavigate();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!isLoading && user) {
    return <Navigate to="/" replace />;
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    try {
      await register(name, email, password, passwordConfirmation);
      navigate('/', { replace: true });
    } catch (err) {
      if (axios.isAxiosError(err) && err.response?.status === 422) {
        const errors = err.response.data?.errors as Record<string, string[]> | undefined;
        const firstMessage = errors ? Object.values(errors)[0]?.[0] : undefined;
        setError(firstMessage ?? '入力内容を確認してください。');
      } else {
        setError('登録に失敗しました。時間をおいて再度お試しください。');
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="app-guest-wrap">
      <div className="app-panel app-guest-panel">
        <h1 className="app-page-title">新規登録</h1>
        <form onSubmit={handleSubmit} className="app-form" autoComplete="off">
          <div className="app-form-field">
            <label htmlFor="name">名前</label>
            <input
              id="name"
              name="register-name"
              type="text"
              className="app-input"
              value={name}
              onChange={(event) => setName(event.target.value)}
              required
              autoComplete="off"
            />
          </div>
          <div className="app-form-field">
            <label htmlFor="email">メールアドレス</label>
            <input
              id="email"
              name="register-email"
              type="email"
              className="app-input"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              required
              autoComplete="off"
            />
          </div>
          <div className="app-form-field">
            <label htmlFor="password">パスワード</label>
            <input
              id="password"
              name="register-password"
              type="password"
              className="app-input"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              required
              autoComplete="off"
            />
          </div>
          <div className="app-form-field">
            <label htmlFor="password_confirmation">パスワード（確認）</label>
            <input
              id="password_confirmation"
              name="register-password-confirmation"
              type="password"
              className="app-input"
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              required
              autoComplete="off"
            />
          </div>
          {error && <p className="app-error">{error}</p>}
          <div className="app-form-actions">
            <button type="submit" className="app-btn app-btn--primary" disabled={isSubmitting}>
              {isSubmitting ? '登録中...' : '登録する'}
            </button>
          </div>
        </form>
        <p className="app-guest-switch">
          すでにアカウントをお持ちの方は <Link to="/login">ログイン</Link>
        </p>
      </div>
    </div>
  );
}
