{{-- One turn: the stones drawn, then the stone laid or the pass. --}}
@props(['turn', 'game', 'editable' => false, 'isCreator' => false])
<tbody class="border-b-2 border-stone-100 last:border-0">
    @foreach ($turn['events'] as $event)
        <x-game.event-row :event="$event" :game="$game" :editable="$editable" :is-creator="$isCreator" />
    @endforeach

    @if (count($turn['events']) > 1 && ! $turn['game_end'])
        <tr class="bg-stone-50">
            <td></td>
            <td colspan="2" class="px-2 py-1.5 text-xs text-stone-500">
                Totaal van de beurt van {{ $turn['player']->name() }}@if ($turn['open']) (beurt nog bezig)@endif
            </td>
            <td @class(['whitespace-nowrap px-2 py-1.5 text-right text-sm font-bold tabular-nums', 'text-rose-600' => $turn['total'] < 0, 'text-blue-700' => $turn['total'] >= 0])>
                {{ $turn['total'] > 0 ? '+' : '' }}{{ $turn['total'] }}
            </td>
            <td></td>
        </tr>
    @endif
</tbody>
