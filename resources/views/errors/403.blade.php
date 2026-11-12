@extends('errors.layout')

@section('code', '403')
@section('titre', 'Accès non autorisé')
@section('message', "Ce contenu appartient à une autre division. La séparation des données est active : un compte Solde ne peut pas consulter les dossiers Visa, et inversement. Seuls l'administrateur et le chef de service (hiérarchique) voient toutes les divisions.")
