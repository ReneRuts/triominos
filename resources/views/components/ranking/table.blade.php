@props(['title', 'rows', 'empty' => 'Nog geen afgeronde spellen in deze selectie.'])
<x-ui.card :title="$title" :padding="false" class="overflow-x-auto">
    <table class="w-full min-w-[520px] text-sm">
        <thead>
            <tr class="border-b border-stone-200 text-left text-xs uppercase tracking-wide text-stone-500">
                <th class="px-4 py-3">#</th>
                <th class="px-3 py-3">Speler</th>
                <th class="px-3 py-3 text-right">Gewonnen</th>
                <th class="px-3 py-3 text-right">Gespeeld</th>
                <th class="px-3 py-3 text-right">Winst %</th>
                <th class="px-3 py-3 text-right">Gem. punten</th>
                <th class="px-4 py-3 text-right">Totaal punten</th>
            </tr>
        </thead>
        <tbody>
            @php $place = 0; $prev = null; @endphp
            @forelse ($rows as $i => $row)
                @php
                    $key = $row->wins.'|'.$row->total_points;
                    if ($key !== $prev) { $place = $i + 1; $prev = $key; }
                @endphp
                <tr @class(['border-b border-stone-100 last:border-0', 'bg-blue-50/60' => (int) $row->user_id === (int) auth()->id()])>
                    <td class="px-4 py-3 font-semibold text-stone-500">{{ $place }}</td>
                    <td class="px-3 py-3 font-medium">{{ $row->username }}</td>
                    <td class="px-3 py-3 text-right font-semibold tabular-nums">{{ $row->wins }}</td>
                    <td class="px-3 py-3 text-right tabular-nums text-stone-600">{{ $row->games_played }}</td>
                    <td class="px-3 py-3 text-right tabular-nums text-stone-600">{{ $row->win_rate }}%</td>
                    <td class="px-3 py-3 text-right tabular-nums text-stone-600">{{ number_format($row->avg_points, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-stone-600">{{ number_format($row->total_points, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-stone-500">{{ $empty }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="border-t border-stone-100 px-4 py-2 text-xs text-stone-400">Gesorteerd op gewonnen, daarna op totaal aantal punten. Gelijke stand = gedeelde plaats.</p>
</x-ui.card>
