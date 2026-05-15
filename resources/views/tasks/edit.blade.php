@extends('layouts.app')

@section('title', 'タスク編集')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">タスク編集</h1>

    <form method="post" action="{{ route('tasks.update', $task->id) }}" class="max-w-xl space-y-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#161615]">
        @csrf
        @method('PUT')
        @include('tasks._form', ['task' => $task])

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-[#1b1b18] px-4 py-2 text-sm font-medium text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]">
                更新
            </button>
            <a href="{{ route('tasks.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm dark:border-[#3E3E3A]">一覧へ</a>
        </div>
    </form>
@endsection