<section>
  <x-section-title description="アカウントの名前とメールアドレスを更新できます。">
    プロフィール情報
  </x-section-title>

  <form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
  </form>

  <form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="app-form-field">
      <x-input-label for="name" value="名前" />
      <x-text-input id="name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
      <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="app-form-field">
      <x-input-label for="email" value="メールアドレス" />
      <x-text-input id="email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required autocomplete="username" />
      <x-input-error class="mt-2" :messages="$errors->get('email')" />

      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div>
          <p class="mt-2 text-sm text-gray-800">
            メールアドレスが未認証です。

            <button form="send-verification" class="app-link">
              認証メールを再送する
            </button>
          </p>

          @if (session('status') === 'verification-link-sent')
            <x-flash-message class="mt-2">
              認証用リンクをメールで送信しました。
            </x-flash-message>
          @endif
        </div>
      @endif
    </div>

    <div class="app-form-actions app-form-actions--center">
      <x-primary-button>保存</x-primary-button>

      @if (session('status') === 'profile-updated')
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
