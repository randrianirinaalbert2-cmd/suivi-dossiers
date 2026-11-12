@extends('errors.layout')

@section('code', '422')
@section('titre', 'Action impossible')
@section('message', "La demande n'est plus valable : la page était sans doute restée ouverte ou l'action a déjà été enregistrée. Rechargez la page puis réessayez.")
