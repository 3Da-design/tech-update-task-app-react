<x-guest-layout>
  <x-slot name="header">ログイン</x-slot>

  <p class="mb-6 text-center text-sm text-gray-600">アカウント情報を入力してください</p>

  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('login') }}" class="space-y-1">
    @csrf

    <div class="app-form-field">
      <x-input-label for="email" value="メールアドレス" />
      <x-text-input
        id="email"
        class="block w-full"
        type="email"
        name="email"
        :value="old('email')"
        required
        autofocus
        autocomplete="username"
        placeholder="you@example.com"
      />
      <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="app-form-field">
      <x-input-label for="password" value="パスワード" />
      <x-text-input
        id="password"
        class="block w-full"
        type="password"
        name="password"
        required
        autocomplete="current-password"
      />
      <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="mb-6 flex items-center justify-between">
      <label for="remember_me" class="inline-flex cursor-pointer items-center">
        <input
          id="remember_me"
          type="checkbox"
          class="app-checkbox"
          name="remember"
        >
        <span class="ms-2 text-sm text-gray-600">ログイン状態を保持</span>
      </label>

      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="app-link">
          パスワードを忘れた方
        </a>
      @endif
    </div>

    <x-primary-button class="w-full justify-center">
      ログイン
    </x-primary-button>
  </form>

  @if (Route::has('register'))
    <p class="mt-8 border-t border-gray-200 pt-6 text-center text-sm text-gray-600">
      アカウントをお持ちでない方は
      <a href="{{ route('register') }}" class="app-link">新規登録</a>
    </p>
  @endif
</x-guest-layout>
