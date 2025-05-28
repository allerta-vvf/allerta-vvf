<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>AllertaVvf</title>
  <base href="{{ asset('/') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="{{ asset('/favicon.ico') }}">
  <link rel="manifest" href="{{ asset('/manifest.webmanifest') }}">
  <meta name="theme-color" content="#1976d2">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link href="{{ asset('/css/style.css') }}" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
@section('navigation')
<div class="topnav" id="topNavBar">
  <a href="{{ URL::r('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ ucfirst(__('app.menu.list')) }}</a>
  @permission('services-read')<a href="{{ URL::r('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">{{ ucfirst(__('app.menu.services')) }}</a>@endpermission
  @permission('trainings-read')<a href="{{ URL::r('trainings') }}" class="{{ request()->routeIs('trainings') ? 'active' : '' }}">{{ ucfirst(__('app.menu.trainings')) }}</a>@endpermission
  @permission('logs-read')<a href="{{ URL::r('logs') }}" class="{{ request()->routeIs('logs') ? 'active' : '' }}">{{ ucfirst(__('app.menu.logs')) }}</a>@endpermission
  @permission('services-read')<a href="{{ URL::r('stats') }}" class="{{ request()->routeIs('stats') ? 'active' : '' }}">{{ ucfirst(__('app.menu.stats')) }}</a>@endpermission
  @permission('admin-read')<a href="{{ URL::r('admin') }}" class="{{ request()->routeIs('admin') ? 'active' : '' }}">{{ ucfirst(__('app.menu.admin')) }}</a>@endpermission
  <a style="float: right;" id="logout" href="{{ URL::r('logout') }}">{{ ucfirst(__('app.menu.hi')) }}, {{ Auth::user()->name ?? ucfirst(__('app.profile.name')) }}. <b id="logout-text">{{ ucfirst(__('auth.logout')) }}</b></a>
  <a class="icon" id="menuButton">☰</a>
</div>
<script>
  document.getElementById("menuButton").onclick = function() {
    var x = document.getElementById("topNavBar");
    if (x.className === "topnav responsive") {
      x.className = "topnav";
    } else {
      x.className = "topnav responsive";
    }
  }
</script>
@show
<script>
  // Set current user ID if user is logged in
  @if(Auth::check())
  window.currentUserId = {{ Auth::id() }};
  @else
  window.currentUserId = null;
  @endif
  // Set API root
  window.apiRoot = "{{ asset('api') }}/";
</script>

<div class="container">
  @yield('content')
</div>

<div id="footer" class="footer text-center p-3">
  {{ __('app.footer.text') }}<br>
  @php
    $gitVersionFile = storage_path('app/git-version.json');
    $gitVersion = null;
    if (file_exists($gitVersionFile)) {
        $gitVersion = json_decode(file_get_contents($gitVersionFile), true);
    }
  @endphp
  @if($gitVersion)
    <p>
      {{ __('app.footer.revision') }} <a href="{{ $gitVersion['remote_url'] }}" class="text-decoration-none text-body">{{ $gitVersion['revision'] }}</a>
      {{ date('Y-m-d H:i', $gitVersion['revision_timestamp']/1000) }}
    </p>
  @else
    <p>{{ __('app.footer.version_info_not_available') }}</p>
  @endif
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
