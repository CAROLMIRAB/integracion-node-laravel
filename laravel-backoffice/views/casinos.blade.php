@extends('template')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('packages/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css') }}">
    <link rel="stylesheet"
          href="{{ asset('packages/datatables-tabletools/css/dataTables.tableTools.css') }}">
    <link rel="stylesheet"
          href="{{ asset('packages/iCheck/skins/flat/grey.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered profit-items">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-users"></i>
                        <span class="caption-subject font-blue-madison bold uppercase"> {{ __('Casinos') }}</span>
                    </div>

                </div>
                <div class="portlet-body">
                    <div class="table-toolbar">
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                <div class="btn-group">
                                    <button id="newCasino" data-toggle="modal" type="button" class="btn green"
                                            href="#modal-create-casino">
                                        <i class="fa fa-plus"></i> {{  __('Nuevo') }}
                                    </button>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            </div>
                        </div>
                    </div>
                    <div class="col1">
                        <table id="datatable-users"
                               class="table table-bordered table-striped table-condensed flip-content"
                               data-route="{{ route('realcasino.casinosdata') }}">
                            <thead class="flip-content">
                            <tr>
                                <th align="center">{{ __('Casino') }}</th>
                                <th align="center">{{ __('Description') }}</th>
                                <th align="center">{{ __('Acciones') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('realcasino.modals.create_casino')
@endsection

@section('scripts')
    <script src="{{ asset('packages/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js') }}"></script>
    <script src="{{ asset('packages/datatables-plugins/api/fnReloadAjax.js') }}"></script>
    <script src="{{ asset('js/realcasino.js') }}"></script>
    <script>
        $(function () {
            RealCasino.casinosDataTable();
            RealCasino.initCasinoFormCreate();
        });
    </script>
@endsection