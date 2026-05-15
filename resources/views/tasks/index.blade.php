@extends('layouts.app')

@section('title', 'タスク一覧')

@section('content')
  <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h1 class="text-xl font-semibold">タスク一覧</h1>
    <a
      href="{{ route('tasks.create') }}"
      class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:bg-[#1F1F1C]"
    >
      新規作成
    </a>
  </div>

  {{-- 検索・フィルタ --}}
  <form
    method="get"
    action="{{ route('tasks.index') }}"
    class="mb-6 flex flex-wrap items-end gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]"
  >
    <div class="flex flex-col gap-1">
      <label for="filter-title" class="text-xs font-medium text-gray-600 dark:text-gray-400">タイトル</label>
      <input
        id="filter-title"
        type="search"
        name="title"
        value="{{ old('title', request('title')) }}"
        class="min-w-[12rem] rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
      >
      @error('title')
        <span class="text-xs text-red-600">{{ $message }}</span>
      @enderror
    </div>

    <div class="flex flex-col gap-1">
      <label for="filter-status" class="text-xs font-medium text-gray-600 dark:text-gray-400">ステータス</label>
      <select
        id="filter-status"
        name="status"
        class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
      >
        <option value="">すべて</option>
        @foreach (config('task.status_values') as $status)
          <option value="{{ $status }}" @selected(old('status', request('status')) === $status)>
            {{ $status }}
          </option>
        @endforeach
      </select>
      @error('status')
        <span class="text-xs text-red-600">{{ $message }}</span>
      @enderror
    </div>

    <div class="flex flex-col gap-1">
      <span class="text-xs font-medium text-gray-600 dark:text-gray-400">期限並び替え</span>
      <div class="flex gap-2">
        <label class="inline-flex items-center gap-1 text-sm">
          <input
            type="radio"
            name="due_date_sort"
            value="asc"
            @checked(old('due_date_sort', request('due_date_sort', 'asc')) === 'asc')
          >
          昇順
        </label>
        <label class="inline-flex items-center gap-1 text-sm">
          <input
            type="radio"
            name="due_date_sort"
            value="desc"
            @checked(old('due_date_sort', request('due_date_sort')) === 'desc')
          >
          降順
        </label>
      </div>
      @error('due_date_sort')
        <span class="text-xs text-red-600">{{ $message }}</span>
      @enderror
    </div>

    <button
      type="submit"
      class="rounded-md bg-[#1b1b18] px-4 py-2 text-sm font-medium text-white hover:bg-black dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
    >
      適用
    </button>
  </form>

  <!-- {{-- 並び替えだけリンクで切り替えたい場合（フィルタ維持） --}}
  <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
    クイック切替:
    <a href="{{ route('tasks.index', array_merge(request()->except('due_date_sort'), ['due_date_sort' => 'asc'])) }}" class="underline">期限 昇順</a>
    ・
    <a href="{{ route('tasks.index', array_merge(request()->except('due_date_sort'), ['due_date_sort' => 'desc'])) }}" class="underline">期限 降順</a>
  </p> -->

  {{-- 表 --}}
  <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-[#3E3E3A] dark:bg-[#161615]">
    <table class="min-w-full text-left text-sm">
      <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-600 dark:border-[#3E3E3A] dark:bg-[#1f1f1c] dark:text-gray-400">
        <tr>
          <th class="px-4 py-3">タイトル</th>
          <th class="px-4 py-3">ステータス</th>
          <th class="px-4 py-3">期限</th>
          <th class="px-4 py-3 text-right">操作</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($tasks as $task)
          <tr class="border-b border-gray-100 last:border-0 dark:border-[#2A2A28]">
            <td class="px-4 py-3 font-medium">{{ $task->title }}</td>
            <td class="px-4 py-3">{{ $task->status }}</td>
            <td class="px-4 py-3">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
            <td class="px-4 py-3 text-right">
              <a
                href="{{ route('tasks.edit', $task->id) }}"
                class="mr-2 text-sm text-blue-700 underline dark:text-blue-400"
              >
                編集
              </a>

              <form
                method="post"
                action="{{ route('tasks.destroy', $task->id) }}"
                class="inline"
                onsubmit="return confirm('このタスクを削除しますか？');"
              >
                @csrf
                @method('DELETE')
                <button
                  type="submit"
                  class="text-sm text-red-600 underline dark:text-red-400"
                >
                  削除
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-4 py-8 text-center text-gray-500">タスクがありません</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection