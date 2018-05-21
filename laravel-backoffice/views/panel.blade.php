@extends('template')

@section('styles')
    <link rel="stylesheet" href="{{ asset('packages/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/other.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/datatable-loader.css') }}"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered" data-load="true">
                <div class="portlet-title">
                    <div class="caption caption-md">
                        <span class="caption-subject font-blue-madison bold uppercase">{{ __('Panel de Administración') }}</span>
                    </div>
                    <div class="tools"></div>
                </div>
                <div class="portlet-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed table-striped datatable-transactions"
                               data-route="{{ route("realcasino.administrationusers") }}">
                            <thead>
                            <tr class="font flip-content">
                                <th width="6%">{{ __('Usuario') }}</th>
                                <th width="6%">{{ __('Acciones') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('realcasino.modals.deposit')
@endsection

@section('scripts')
    <script src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('packages/datatables-plugins/api/fnReloadAjax.js') }}"></script>
    <script src="{{ asset('packages/jquery-validation/dist/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('packages/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('packages/jquery-datatables-columnfilter/jquery.dataTables.columnFilter.js') }}"></script>
    <script src="{{ asset('packages/jquery-number/jquery.number.min.js') }}"></script>
    <script src="{{ asset('packages/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
    <script src="{{ asset('js/realcasino.js') }}"></script>
    <script>

        $(function () {
            RealCasino.dateRangePicker();
            RealCasino.panelAdministrationUsers();
        });
    </script>
@endsection