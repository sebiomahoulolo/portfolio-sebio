@extends('errors.layout')

@section('code', '419')
@section('title', 'Session expirée')
@section('message', 'Par sécurité, votre session a expiré après une période d’inactivité. Rechargez la page pour continuer.')

@section('actions')
    <button type="button" class="btn btn-primary" onclick="history.back()">{{ __('Revenir et recharger') }}</button>
    <a href="{{ url('/') }}" class="btn">{{ __('Retour à l’accueil') }}</a>
@endsection
