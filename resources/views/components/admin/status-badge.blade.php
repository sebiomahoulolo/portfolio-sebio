@props(['status'])

{{-- Affiche n'importe quel enum de statut exposant label() et color(). --}}
<flux:badge size="sm" :color="$status->color()" inset="top bottom">{{ $status->label() }}</flux:badge>
