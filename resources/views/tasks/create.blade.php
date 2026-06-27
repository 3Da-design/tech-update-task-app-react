<x-app-layout>
  <x-app-container narrow>
    <x-page-heading title="タスク作成" />
    <x-card>
      <form method="post" action="{{ route('tasks.store') }}">
        @csrf
        @include('tasks._form', ['task' => null])

        <div class="app-form-actions app-form-actions--center">
          <x-primary-button>保存</x-primary-button>
          <x-secondary-link href="{{ route('tasks.index') }}">キャンセル</x-secondary-link>
        </div>
      </form>
    </x-card>
  </x-app-container>
</x-app-layout>
