{{-- One row in the list of score events. --}}
@props(['event', 'game', 'editable' => false, 'isCreator' => false])
<tr @class(['border-b border-stone-100 last:border-0', 'bg-stone-50' => $event->locked])>
    <td class="whitespace-nowrap py-2 pl-4 pr-2 text-xs text-stone-400">{{ $event->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
    <td class="px-2 py-2 text-sm font-medium">{{ $event->player->name() }}</td>
    <td class="px-2 py-2 text-sm">
        {{ $event->label() }}
        @if ($event->summary() && in_array($event->type->value, ['tile', 'game_end'], true))
            <span class="block text-xs text-stone-500">{{ $event->summary() }}</span>
        @endif
    </td>
    <td @class(['whitespace-nowrap px-2 py-2 text-right text-sm font-semibold tabular-nums', 'text-rose-600' => $event->points < 0, 'text-blue-700' => $event->points >= 0])>
        {{ $event->points > 0 ? '+' : '' }}{{ $event->points }}
    </td>
    <td class="whitespace-nowrap py-2 pl-2 pr-4 text-right">
        <div class="flex items-center justify-end gap-1">
            @if ($event->locked)
                <span title="Vastgezet door de spelmaker" class="text-stone-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4 4 0 0 0-4 4v2H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-1V5a4 4 0 0 0-4-4Zm2 6V5a2 2 0 1 0-4 0v2h4Z" clip-rule="evenodd"/></svg>
                </span>
            @endif

            @if ($isCreator && $game->isActive())
                <form method="POST" action="{{ route('events.lock', [$game, $event]) }}">@csrf
                    <button class="rounded px-2 py-1 text-xs text-stone-500 hover:bg-stone-100">{{ $event->locked ? 'Losmaken' : 'Vastzetten' }}</button>
                </form>
            @endif

            @if ($editable && ! $event->locked && $event->type->value !== 'game_end')
                <form method="POST" action="{{ route('events.destroy', [$game, $event]) }}" x-data="{ sure: false }">
                    @csrf @method('DELETE')
                    <button type="button" x-show="!sure" @click="sure = true" class="rounded px-2 py-1 text-xs text-stone-500 hover:bg-rose-50 hover:text-rose-600">Verwijderen</button>
                    <button x-show="sure" x-cloak class="rounded bg-rose-600 px-2 py-1 text-xs text-white">Zeker?</button>
                </form>
            @endif
        </div>
    </td>
</tr>
