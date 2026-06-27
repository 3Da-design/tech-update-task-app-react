<section>
  <x-section-title description="十分に長くランダムなパスワードを設定してください。">
    パスワード変更
  </x-section-title>

  <form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="app-form-field">
      <x-input-label for="update_password_current_password" value="現在のパスワード" />
      <x-text-input id="update_password_current_password" name="current_password" type="password" class="block w-full" autocomplete="current-password" />
      <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    <div class="app-form-field">
      <x-input-label for="update_password_password" value="新しいパスワード" />
      <x-text-input id="update_password_password" name="password" type="password" class="block w-full" autocomplete="new-password" />
      <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    </div>

    <div class="app-form-field">
      <x-input-label for="update_password_password_confirmation" value="新しいパスワード（確認）" />
      <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full" autocomplete="new-password" />
      <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    </div>

    <div class="app-form-actions app-form-actions--center">
      <x-primary-button>保存</x-primary-button>

      @if (session('status') === 'password-updated')
        <p
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 2000)"
          class="text-sm text-gray-600"
        >
          保存しました。
        </p>
      @endif
    </div>
  </form>
</section>
