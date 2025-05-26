@extends('layout.base')

@section('content')
<div class="container">
    <h1>{{ ucfirst(trans_choice(__('app.service'),2)) }}</h1>
    <div class="text-center mb-4">
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            {{ ucfirst(__('app.add', ['item' => trans_choice(__('app.service'),1)])) }}
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ ucfirst(__('app.code')) }}</th>
                <th>{{ ucfirst(__('app.type')) }}</th>
                <th>{{ ucfirst(__('app.chief')) }}</th>
                <th>{{ ucfirst(__('app.start')) }}</th>
                <th>{{ ucfirst(__('app.end')) }}</th>
                <th>{{ ucfirst(__('app.notes')) }}</th>
                <th>{{ ucfirst(__('app.actions')) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service['id'] }}</td>
                    <td>{{ $service['code'] }}</td>
                    <td>{{ $service['type'] }}</td>
                    <td>{{ $service['chief'] }}</td>
                    <td>{{ $service['start'] }}</td>
                    <td>{{ $service['end'] }}</td>
                    <td>{{ $service['notes'] }}</td>
                    <td>
                        <a href="{{ route('services.edit', $service['id']) }}" class="btn btn-sm btn-info">{{ ucfirst(__('app.details')) }}</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection