@extends('template')

@section('styles')
    <link rel="stylesheet" href="{{ asset('packages/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    @if(Session::has('error_message'))
        @foreach(Session::get('error_message')->all() as $error)
            <div class="alert alert-warning alert-dismissable">
                <i class="fa fa-warning alert-icon"></i>
                <button type="button" class="close" data-dismiss="alert"
                        aria-hidden="true">&times;</button>
                <strong>{{ $error }}</strong>
            </div>
        @endforeach
    @endif

    @if(Session::has('success_message'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa fa-check-circle-o alert-icon"></i>
            <button type="button" class="close" data-dismiss="alert"
                    aria-hidden="true">&times;</button>
            <strong>{{ Session::get('success_message') }}</strong>
        </div>
    @endif

    @if(Session::has('error_string'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-check-circle-o alert-icon"></i>
            <button type="button" class="close" data-dismiss="alert"
                    aria-hidden="true">&times;</button>
            <strong>{{ Session::get('error_string') }}</strong>
        </div>
    @endif
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject font-blue-madison bold uppercase">{{ __('Crear usuario') }}</span>
            </div>
        </div>
        <div class="portlet-body">
            <form class="form-horizontal" action="{{route('realcasino.create-user-post')}}" method="POST"
                  id="create-user-casino">
                <div class="form-group">
                    <label for="username" class="col-md-2 control-label">{{ __('Usuario') }}</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="{{ __('Usuario') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-2 control-label">{{ __('Contraseña') }}</label>
                    <div class="col-md-4">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="{{ __('Contraseña') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-2 control-label">{{ __('Correo electrónico') }}</label>
                    <div class="col-md-4">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="{{ __('Correo electrónico') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="country" class="col-md-2 control-label">{{ __('País') }}</label>
                    <div class="col-md-4">
                        <select class="form-control" id="country" name="country">
                            <option value="">{{ __('Seleccione...') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->value  }}">{{ $country->text }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="timezone" class="col-md-2 control-label">{{ __('Zona horaria') }}</label>
                    <div class="col-md-4">
                        <select class="form-control" id="timezone" name="timezone">
                            <option value="">{{ __('Seleccione') }}</option>
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone  }}">{{ $timezone  }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8">
                            <button type="submit" class="btn blue btn-create" data-loading-text="<i class='fa fa-spin fa-spinner'></i>">{{ __('Crear') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('packages/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/realcasino.js') }}"></script>
    <script>
        $(document).on('ready', function () {
            $('#timezone, #country').select2();
        });
        RealCasino.createUserCasino();
    </script>
@endsection
