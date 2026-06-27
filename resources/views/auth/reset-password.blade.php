<x-guest-layout>
  <x-slot name="header">パスワード再設定</x-slot>

  <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
      <x-input-label for="email" value="メールアドレス" />
      <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
      <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
      <x-input-label for="password" value="新しいパスワード" />
      <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
      <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
      <x-input-label for="password_confirmation" value="新しいパスワード（確認）" />
      <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
      <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <x-primary-button class="w-full justify-center">
      パスワードを更新
    </x-primary-button>
  </form>
</x-guest-layout>
