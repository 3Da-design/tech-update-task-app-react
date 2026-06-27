<section class="space-y-6">
  <x-section-title description="アカウントを削除すると、すべてのデータが完全に削除されます。必要なデータは事前に保存してください。">
    アカウント削除
  </x-section-title>

  <x-danger-button
    x-data=""
    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
  >
    アカウントを削除
  </x-danger-button>

  <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
      @csrf
      @method('delete')

      <h2 class="text-lg font-semibold text-gray-900">
        アカウントを削除しますか？
      </h2>

      <p class="mt-1 text-sm text-gray-600">
        削除を確定するには、パスワードを入力してください。
      </p>

      <div class="mt-6">
        <x-input-label for="password" value="パスワード" class="sr-only" />

        <x-text-input
          id="password"
          name="password"
          type="password"
          class="mt-1 block w-3/4"
          placeholder="パスワード"
        />

        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <x-secondary-button x-on:click="$dispatch('close')">
          キャンセル
        </x-secondary-button>

        <x-danger-button>
          アカウントを削除
        </x-danger-button>
      </div>
    </form>
  </x-modal>
</section>
