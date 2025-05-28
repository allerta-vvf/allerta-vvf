@extends('layout.base')

@section('content')
<style>
#availability-btn-group {
    margin: 10px;
}

#activate-btn {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
}
#activate-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

#deactivate-btn {
    color: #fff;
    background-color: #fd0019;
    border-color: #fd0019;
}
#deactivate-btn:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}
.owner_image {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 150px;
}
</style>
<div class="text-center">
    <h3><b>{{ $available ? strtoupper(__('app.available')) : strtoupper(__('app.unavailable')) }}{{ $availability_manual_mode ? '' : ' ('.__('app.availability_programmed').')' }}</b></h3>
    <div id="availability-btn-group">
        <button onclick="changeAvailability(1)" type="button" id="activate-btn" class="btn btn-lg btn-success me-1">{{ __('list.set_available') }}</button>
        <button onclick="changeAvailability(0)" type="button" id="deactivate-btn" class="btn btn-lg btn-danger">{{ __('list.set_unavailable') }}</button>
    </div>
    @if(isset($availability_manual_mode))
        @if($availability_manual_mode)
            <button type="button" class="btn btn-secondary" onclick="updateManualMode(0)">
                {{ __('list.enable_schedules') }}
            </button>
        @else
            <button type="button" class="btn btn-secondary" onclick="updateManualMode(1)">
                {{ __('list.disable_schedules') }}
            </button>
        @endif
        <br>
    @endif
    <button type="button" class="btn btn-lg" onclick="openScheduleModal()">
        {{ __('list.update_schedules') }}
    </button>
</div>
<script>
function changeAvailability(available, id) {
    if (typeof id === 'undefined') {
        id = window.currentUserId || null;
    }
    fetch(`${window.apiRoot}availability`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: id, available: available })
    })
    .then(response => response.json())
    .then(data => {
        let changed_user_msg = (parseInt(data.updated_user_id) === parseInt(window.currentUserId))
            ? "{{ ucfirst(__('app.your_availability')) }}"
            : `{{ ucfirst(__('app.user_availability', ['name' => '${data.updated_user_name}'])) }}`;
        let msg = available === 1
            ? changed_user_msg + " {{ __('list.set_success') }}"
            : changed_user_msg + " {{ __('list.removed_success') }}";
        toastr.success(msg);
        //TODO: refresh the list
    })
    .catch(err => {
        if (err.status === 500) throw err;
        toastr.error("{{ __('list.availability_change_failed') }}");
    });
}

function updateManualMode(manual_mode) {
    fetch(`${window.apiRoot}manual_mode`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ manual_mode: manual_mode })
    })
    .then((data) => {
        toastr.success("{{ __('list.manual_mode_updated_successfully') }}");
        if (typeof loadAvailability === 'function') loadAvailability();
    })
    .catch((err) => {
        if (err.status === 500) throw err;
        toastr.error("{{ __('list.manual_mode_update_failed') }}");
    });
}

function openScheduleModal() {
    // Implement schedule modal opening
    console.log('Opening schedule modal');
}
</script>
<img src="{{ asset('api/owner_image') }}" alt="Logo" class="owner_image">
<div id="list" class="table-responsive mt-4">
    <table id="table" class="table table-striped table-bordered dt-responsive nowrap">
        <thead>
            <tr>
                <th>{{ ucfirst(__('app.name')) }}</th>
                <th>{{ ucfirst(__('app.available')) }}</th>
                <th>{{ ucfirst(__('app.driver')) }}</th>
                @permission('users-read')
                    <th>{{ ucfirst(__('app.call')) }}</th>
                @endpermission
                <th>{{ ucfirst(trans_choice(__('app.service'),2)) }}</th>
                <th>{{ ucfirst(__('app.availability_minutes')) }}</th>
                @permission('users-update')
                    <th>{{ ucfirst(__('app.edit')) }}</th>
                @endpermission
            </tr>
        </thead>
        <tbody id="table_body">
            @foreach($users ?? [] as $row)
                <tr>
                    <td>
                        @permission('users-impersonate')
                            @if($row->id !== auth()->id())
                                <i class="fa fa-user me-2" onclick="impersonateUser({{ $row->id }})"></i>
                            @endif
                        @endpermission
                        <div onclick="showMoreDetails({{ $row->id }})" class="d-inline">
                            @if($row->chief)
                                <img alt="red helmet" src="{{ asset('icons/red_helmet.png') }}" width="20px">
                            @else
                                <img alt="black helmet" src="{{ asset('icons/black_helmet.png') }}" width="20px">
                            @endif
                            @if($row->online)
                                <u>{{ $row->surname }} {{ $row->name }}</u>
                            @else
                                {{ $row->surname }} {{ $row->name }}
                            @endif
                        </div>
                    </td>
                    <td onclick="changeAvailability({{ $row->available ? 0 : 1 }}, {{ $row->id }})">
                        @if($row->available)
                            <i class="fa fa-check" style="color:green"></i>
                        @else
                            <i class="fa fa-times" style="color:red"></i>
                        @endif
                    </td>
                    <td>
                        @if($row->driver)
                            <img alt="driver" src="{{ asset('icons/wheel.png') }}" width="20px">
                        @endif
                    </td>
                    @permission('users-read')
                        <td>
                            @if($row->phone_number)
                                <a href="tel:{{ $row->phone_number }}"><i class="fa fa-phone"></i></a>
                            @endif
                        </td>
                    @endpermission
                    <td>{{ $row->services }}</td>
                    <td>{{ $row->availability_minutes }}</td>
                    @permission('users-update')
                        <td onclick="editUser({{ $row->id }})"><i class="fa fa-edit"></i></td>
                    @endpermission
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function showMoreDetails(userId) {
    // Implement user details functionality
    console.log('Show details for user:', userId);
}

function editUser(userId) {
    // Implement edit user functionality
    console.log('Edit user:', userId);
}

function impersonateUser(userId) {
    // Implement user impersonation
    console.log('Impersonate user:', userId);
}
</script>
@endsection