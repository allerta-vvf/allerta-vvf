@extends('layout.base')

@section('content')
<div class="container">
    <h1>{{ ucfirst(trans_choice(__('app.training'),2)) }}</h1>
    <div class="text-center mb-4">
        <a href="{{ route('trainings.create') }}" class="btn btn-primary">
            {{ ucfirst(__('app.add', ['item' => trans_choice(__('app.training'),1)])) }}
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ ucfirst(__('app.name')) }}</th>
                <th>{{ ucfirst(__('app.start')) }}</th>
                <th>{{ ucfirst(__('app.end')) }}</th>
                <th>{{ ucfirst(__('app.chief')) }}</th>
                <th>{{ ucfirst(__('app.crew')) }}</th>
                <th>{{ ucfirst(__('app.place')) }}</th>
                <th>{{ ucfirst(__('app.notes')) }}</th>
                <th>{{ ucfirst(__('app.actions')) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trainings as $training)
                <tr>
                    <td>{{ $training['id'] }}</td>
                    <td>{{ $training['name'] }}</td>
                    <td>{{ $training['start'] }}</td>
                    <td>{{ $training['end'] }}</td>
                    <td>{{ $training['chief'] }}</td>
                    <td>{{ $training['crew'] }}</td>
                    <td>{{ $training['place'] }}</td>
                    <td>{{ $training['notes'] }}</td>
                    <td>
                        <a href="{{ route('trainings.edit', $training['id']) }}" class="btn btn-sm btn-info">{{ ucfirst(__('app.details')) }}</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection