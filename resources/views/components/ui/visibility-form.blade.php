{{-- Switches between private and public. Shows the current state, the click changes it. --}}
@props(['action', 'isPublic'])
<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="is_public" value="{{ $isPublic ? 0 : 1 }}">
    <x-ui.button variant="secondary" size="sm">{{ $isPublic ? 'Openbaar · maak privé' : 'Privé · maak openbaar' }}</x-ui.button>
</form>