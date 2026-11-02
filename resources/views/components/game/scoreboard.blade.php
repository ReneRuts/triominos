{{-- Score table from a Snapshot: one row per spel, one column per player (in seating order). --}}
@props(['snapshot'])
@php
    $players = $snapshot['players'];
    $target = $snapshot['game']['target_score'];
    $leaders = $snapshot['leaders'];
@endphp
<section class="overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm" aria-label="Stand">
    <table class="w-full table-fixed text-sm" style="min-width: {{ 7 + count($players) * 5.5 }}rem">
        <colgroup>
            <col style="width: 7rem">
            @foreach ($players as $p) <col> @endforeach
        </colgroup>
        <thead>
            <tr class="border-b border-stone-200 text-left">
                <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-stone-500">Spel</th>
                @foreach ($players as $p)
                    <th class="px-3 py-3 font-semibold">
                        <div class="flex min-w-0 items-center justify-end gap-1">
                            @if ($p['is_current'])
                                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500" title="Aan de beurt"></span>
                            @endif
                            <span class="truncate" title="{{ $p['username'] }}">{{ $p['username'] }}</span>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($snapshot['games'] as $g)
                <tr class="border-b border-stone-100">
                    <td class="px-4 py-2 text-stone-500">
                        <span class="block truncate" title="{{ $g['title'] }}">{{ $g['title'] }}</span>
                        @if ($g['status'] === 'active')
                            <x-ui.badge color="blue">bezig</x-ui.badge>
                        @endif
                    </td>
                    @foreach ($players as $p)
                        <td @class(['px-3 py-2 text-right tabular-nums', 'font-semibold text-blue-700' => $g['winner_player_id'] === $p['id']])>
                            {{ $g['totals'][$p['id']] ?? 0 }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($players) + 1 }}" class="px-4 py-4 text-center text-stone-500">Nog geen spellen gespeeld.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-stone-50">
                <td class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-stone-500">Totaal</td>
                @foreach ($players as $p)
                    <td @class(['px-3 py-3 text-right text-lg font-bold tabular-nums', 'text-blue-700' => in_array($p['username'], $leaders, true)])>{{ $p['total'] }}</td>
                @endforeach
            </tr>
            @if ($target)
                <tr>
                    <td class="px-4 pb-3 text-xs text-stone-400">Naar {{ $target }}</td>
                    @foreach ($players as $p)
                        <td class="px-3 pb-3">
                            <div class="h-1.5 rounded-full bg-stone-100">
                                <div class="h-1.5 rounded-full bg-blue-500" style="width: {{ min(100, max(0, round($p['total'] / $target * 100))) }}%"></div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endif
        </tfoot>
    </table>
</section>
