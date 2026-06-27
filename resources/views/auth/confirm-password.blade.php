<x-guest-layout>
  <x-slot name="header">パスワード確認</x-slot>

  <p class="mb-6 text-sm text-gray-600">
    保護された操作のため、続行する前にパスワードを入力してください。
  </p>

  <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
    @csrf

    <div>
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

    <x-primary-button class="w-full justify-center">
      確認
    </x-primary-button>
  </form>
</x-guest-layout>
