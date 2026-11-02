{{-- A checkbox with label and optional hint. :boxed="true" makes it a selectable card. --}}
@props(['name', 'label', 'hint' => null, 'value' => '1', 'checked' => false, 'boxed' => false])
<label {{ $attributes->only('class')->class([
    'flex items-start gap-3 text-sm',
    'rounded-lg border border-stone-200 px-3 py-2 has-checked:border-blue-600 has-checked:bg-blue-50' => $boxed,
]) }}>
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->except('class')->class('mt-0.5 rounded border-stone-300 text-blue-700 focus:ring-blue-500') }}>
    <span>
        <span class="font-medium text-stone-800">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-stone-500">{{ $hint }}</span>
        @endif
    </span>
</label>
