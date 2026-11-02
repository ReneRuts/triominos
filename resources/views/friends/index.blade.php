<x-layout.app title="Vrienden">
    <x-ui.page-header title="Vrienden" subtitle="Vrienden kun je toevoegen aan toernooien en spellen." />

    <div class="grid gap-6 md:grid-cols-2">
        <x-ui.card title="Vriend toevoegen">
            <form method="POST" action="{{ route('friends.store') }}" class="flex items-start gap-2">
                @csrf
                <x-ui.input name="username" label="Gebruikersnaam" class="min-w-0 flex-1" autocomplete="off" required />
                <x-ui.button class="mt-6">Verzoek sturen</x-ui.button>
            </form>

            @if ($outgoing->isNotEmpty())
                <h3 class="mt-6 text-sm font-medium text-stone-500">Verstuurde verzoeken</h3>
                <ul class="mt-2 divide-y divide-stone-100">
                    @foreach ($outgoing as $f)
                        <li class="flex items-center justify-between py-2 text-sm">
                            <span>{{ $f->addressee->username }} <span class="text-stone-400">· wacht op antwoord</span></span>
                            <form method="POST" action="{{ route('friends.destroy', $f) }}">@csrf @method('DELETE')
                                <x-ui.button variant="ghost" size="sm">Annuleren</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card>
            <h2 class="mb-3 font-semibold text-stone-900">Verzoeken
                @if ($incoming->isNotEmpty()) <span class="ml-1 rounded-full bg-rose-500 px-2 py-0.5 text-xs text-white">{{ $incoming->count() }}</span> @endif
            </h2>
            @forelse ($incoming as $f)
                <div class="flex items-center justify-between border-b border-stone-100 py-2 text-sm last:border-0">
                    <span class="font-medium">{{ $f->requester->username }}</span>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('friends.accept', $f) }}">@csrf <x-ui.button size="sm">Accepteren</x-ui.button></form>
                        <form method="POST" action="{{ route('friends.destroy', $f) }}">@csrf @method('DELETE') <x-ui.button size="sm" variant="secondary">Weigeren</x-ui.button></form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500">Geen openstaande verzoeken.</p>
            @endforelse
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <h2 class="mb-3 font-semibold text-stone-900">Mijn vrienden ({{ $friends->count() }})</h2>
        @forelse ($friends as $friend)
            @php $friendship = \App\Models\Friendship::between(auth()->id(), $friend->id); @endphp
            <form method="POST" action="{{ route('friends.destroy', $friendship) }}" class="flex items-center justify-between border-b border-stone-100 py-2 text-sm last:border-0">
                @csrf @method('DELETE')
                <span class="font-medium">{{ $friend->username }}</span>
                <span><x-ui.confirm-button label="Verwijderen" variant="ghost" /></span>
            </form>
        @empty
            <p class="text-sm text-stone-500">Je hebt nog geen vrienden. Zoek iemand op gebruikersnaam om te beginnen.</p>
        @endforelse
    </x-ui.card>
</x-layout.app>
