@props(['turns', 'game', 'title' => 'Verloop', 'editable' => false, 'isCreator' => false])
<x-ui.card :title="$title" :padding="false" class="overflow-hidden">
    @if (count($turns) === 0)
        <p class="px-5 py-4 text-sm text-stone-500">Nog niets genoteerd.</p>
    @else
        <table class="w-full">
            @foreach ($turns as $turn)
                <x-game.turn :turn="$turn" :game="$game" :editable="$editable" :is-creator="$isCreator" />
            @endforeach
        </table>
    @endif
</x-ui.card>
