<x-guest-layout>
  <x-slot name="header">新規登録</x-slot>

  <p class="mb-6 text-center text-sm text-gray-600">アカウントを作成してタスク管理を始めましょう</p>

  <form method="POST" action="{{ route('register') }}" class="space-y-1">
    @csrf

    <div class="app-form-field">
      <x-input-label for="name" value="名前" />
      <x-text-input
        id="name"
        class="block w-full"
        type="text"
        name="name"
        :value="old('name')"
        required
        autofocus
        autocomplete="name"
        placeholder="山田 太郎"
      />
      <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="app-form-field">
      <x-input-label for="email" value="メールアドレス" />
      <x-text-input
        id="email"
        class="block w-full"
        type="email"
        name="email"
        :value="old('email')"
        required
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
        autocomplete="new-password"
      />
      <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="app-form-field">
      <x-input-label for="password_confirmation" value="パスワード（確認）" />
      <x-text-input
        id="password_confirmation"
        class="block w-full"
        type="password"
        name="password_confirmation"
        required
        autocomplete="new-password"
      />
      <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <x-primary-button class="w-full justify-center">
      アカウントを作成
    </x-primary-button>
  </form>

  <p class="mt-8 border-t border-gray-200 pt-6 text-center text-sm text-gray-600">
    すでにアカウントをお持ちの方は
    <a href="/login" class="app-link">ログイン</a>
  </p>
</x-guest-layout>
