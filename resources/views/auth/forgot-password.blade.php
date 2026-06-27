<x-guest-layout>
  <x-slot name="header">パスワード再設定</x-slot>

  <p class="mb-6 text-sm text-gray-600">
    パスワードをお忘れの場合は、登録済みのメールアドレスを入力してください。再設定用のリンクをお送りします。
  </p>

  <x-auth-session-status class="mb-4" :status="session('status')" />

  <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
    @csrf

    <div>
      <x-input-label for="email" value="メールアドレス" />
      <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
      <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <x-primary-button class="w-full justify-center">
      再設定リンクを送信
    </x-primary-button>
  </form>
</x-guest-layout>
