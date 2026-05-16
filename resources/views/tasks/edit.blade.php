<x-app-layout>
    <x-app-container narrow>
        <x-page-heading title="タスク編集" />
        <x-card>
            <form method="post" action="{{ route('tasks.update', $task->id) }}">
                @csrf
                @method('PUT')
                @include('tasks._form', ['task' => $task])

                <div class="app-form-actions">
                    <x-primary-button>更新</x-primary-button>
                    <x-secondary-link href="{{ route('tasks.index') }}">一覧へ</x-secondary-link>
                </div>
            </form>
        </x-card>
    </x-app-container>
</x-app-layout>
