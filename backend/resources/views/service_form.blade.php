@extends('layout.base')

@section('content')
<div class="container">
    <h1>{{ ucfirst(trans_choice(__('app.service'), 1)) }}</h1>
    {{-- Form for add/update service will go here --}}
</div>
@endsection
