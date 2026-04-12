@extends('admin.layouts.app')
@section('page-title', 'Users')
@section('head')

@endsection
@section('content')
<div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.user.index') }}">
                    Users
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    @if(Route::currentRouteName() == 'admin.user.create')
                    Create User
                    @elseif(Route::currentRouteName() == 'admin.user.edit')
                        Edit: {{ $user->name ?? 'user' }}
                    @endif
                </li>
                </ol>
        </nav>
    </div>
<div class="row flex-column-reverse flex-md-row">
    <div class="col-md-12">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="mb-4">
                    <div class="card mb-4">
                        <form action="{{$user->id ? route('admin.user.update',$user->id): route('admin.user.store')}}" method="post">
                            @csrf
                            {{ $user->id? method_field('PUT'):''}}
                                <div class="card-body">
                                    <h6 class="card-phone mb-4">{{ $user->id ? 'Edit' : 'Add' }} User</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input autocomplete="off" type="text" class="form-control" name="name" value="{{old('name', ($user->name ? $user->name : '' ))}}" required>
                                            @if ($errors->has('name'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('name') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email  <span class="text-danger">*</span></label>
                                            <input autocomplete="off" type="email" class="form-control" name="email" value="{{old('email', ($user->email ? $user->email : '' ))}}" required>
                                            @if ($errors->has('email'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('email') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                   <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password @if(!$user->id)<span class="text-danger">*</span>@endif</label>
                                            <input autocomplete="off" type="password" class="form-control" name="password" value="" @if(!$user->id) required @endif>
                                            @if ($errors->has('password'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('password') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    
                                    @if(auth()->user()->hasPermissionTo('View roles'))

                                    <div class="col-md-6">
                                        @php
                                            $selectedRole = $selectedRole ?? [];
                                        @endphp
                                        <div class="mb-3">
                                            <label class="form-label">Roles <span class="text-danger">*</span></label>
                                            <select class="form-select " aria-label="Default" name="role" id="role" >
                                                <option value="">Select Roles</option>
                                                @foreach ($roles as $key=>$role)
                                                <option value="{{ $role['name'] }}" 
                                                {{old('role',$selectedRole ?? '') == $role['name']? 'selected' : ''}}
                                                {{ isset($selectedRole) && $selectedRole == $role['name'] ? 'selected' : '' }}
                                                >
                                                {{ $role['name'] }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('role'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('role') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select " aria-label="Default" name="status" id="status" required>
                                                <option value="1" {{ old('status', $user->status ?? '1') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status', $user->status ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @if ($errors->has('status'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('status') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary mb-3 pull-right">Save</button>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                        </form>

                </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


