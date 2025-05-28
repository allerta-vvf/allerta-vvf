@extends('layout.base')

@section('content')
<div class="container">
    <h1>{{ isset($training) ? ucfirst(__('app.edit')) : ucfirst(__('app.add')) }} {{ __('app.training') }}</h1>
    <form method="POST" action="{{ isset($training) ? route('trainings.update', $training['id']) : route('trainings.store') }}">
        @csrf
        @if(isset($training))
            @method('PUT')
        @endif
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('app.name') }}</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $training['name'] ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label for="start" class="form-label">{{ __('app.start') }}</label>
            <input type="datetime-local" class="form-control" id="start" name="start" value="{{ old('start', $training['start'] ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label for="end" class="form-label">{{ __('app.end') }}</label>
            <input type="datetime-local" class="form-control" id="end" name="end" value="{{ old('end', $training['end'] ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label for="chief" class="form-label">{{ __('app.chief') }}</label>
            <input type="text" class="form-control" id="chief" name="chief" value="{{ old('chief', $training['chief'] ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="crew" class="form-label">{{ __('app.crew') }}</label>
            <input type="text" class="form-control" id="crew" name="crew" value="{{ old('crew', $training['crew'] ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="place" class="form-label">{{ __('app.place') }}</label>
            <input type="text" class="form-control" id="place" name="place" value="{{ old('place', $training['place'] ?? '') }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">{{ __('app.notes') }}</label>
            <textarea class="form-control" id="notes" name="notes">{{ old('notes', $training['notes'] ?? '') }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">{{ __('app.save_changes') }}</button>
        <a href="{{ route('trainings.index') }}" class="btn btn-secondary">{{ __('app.cancel') }}</a>
    </form>
</div>
@endsection
