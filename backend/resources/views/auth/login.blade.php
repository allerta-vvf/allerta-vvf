@extends('layout.base')

@section('navigation')@endsection

@section('content')
    <div class="container text-center d-flex justify-content-center pt-3">
        <main class="form-signin">
            <img class="mb-4" src="{{ asset('api/owner_image') }}" alt="Logo" width="200" height="200">
            <h1 class="h3 mb-3 fw-normal">{{ __('auth.login_to_proceed') }}</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ URL::r('login') }}">
                @csrf
                
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus>
                    <label for="username">{{ ucfirst(__('auth.username')) }}</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">{{ ucfirst(__('auth.password')) }}</label>
                </div>
                
                <button class="w-100 btn btn-lg btn-primary" type="submit">
                    {{ ucfirst(__('auth.login')) }}
                </button>
            </form>
        </main>
    </div>

    <style>
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
@endsection