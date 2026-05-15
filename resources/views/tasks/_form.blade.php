@php
    /** @var \App\Models\Task|null $task */
    $task = $task ?? null;
@endphp

<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-1">
        <label for="title" class="text-sm font-medium">タイトル</label>
        <input
            id="title"
            type="text"
            name="title"
            value="{{ old('title', $task?->title ?? '') }}"
            required
            class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
        >
        @error('title')
            <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex flex-col gap-1">
        <label for="description" class="text-sm font-medium">説明</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
        >{{ old('description', $task?->description ?? '') }}</textarea>
        @error('description')
            <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex flex-col gap-1">
        <label for="status" class="text-sm font-medium">ステータス</label>
        <select
            id="status"
            name="status"
            required
            class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
        >
            @foreach (config('task.status_values') as $status)
                <option value="{{ $status }}" @selected(old('status', $task?->status ?? '') === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex flex-col gap-1">
        <label for="due_date" class="text-sm font-medium">期限</label>
        <input
            id="due_date"
            type="date"
            name="due_date"
            value="{{ old('due_date', $task?->due_date?->format('Y-m-d') ?? '') }}"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
        >
        @error('due_date')
            <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>
</div>