@props(['name', 'label'])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->except('class')->class('w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200') }}>
        {{ $slot }}
    </select>
    @error($name)
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
