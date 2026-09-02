@extends('layouts.layout')
@section('content')
    <div class="header-spacer"></div>

    <div class="container">
        <h1 class="header-middle-center">{{l("Roulette")}}</h1>
        <hr/>

        @include('components.roulette_spinner')
    </div>
@endsection