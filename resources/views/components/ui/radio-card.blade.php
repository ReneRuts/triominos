@props(['name', 'value', 'title', 'description' => null, 'checked' => false])
<label class="flex cursor-pointer gap-3 rounded-xl border border-stone-200 p-3 has-checked:border-blue-600 has-checked:bg-blue-50">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->class('mt-1 text-blue-700 focus:ring-blue-500') }}>
    <span>
        <span class="block text-sm font-medium text-stone-800">{{ $title }}</span>
        @if ($description)
            <span class="text-xs text-stone-500">{{ $description }}</span>
        @endif
    </span>
</label>
