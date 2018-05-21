@extends('template')

@section('styles')
    <link rel="stylesheet" href="{{ asset('packages/datatables-buttons/css/buttons.dataTables.scss') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/other.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/datatable-loader.css') }}"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption caption-md">
                        <span class="caption-subject font-blue-madison bold uppercase">{{ __('Total Profit') }}</span>
                    </div>
                </div>
                <div class="portlet-body" id="totals" data-route="{{ route("realcasino.profitcasinostotal") }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="clearfix">
                                <div class="panel panel-default">
                                    <div class="panel-heading bg-blue-steel font-white">
                                        <h3 class="panel-title"><i class="fa fa-money"></i>
                                            <b>{{ __("Depositado") }}</b>
                                        </h3>
                                    </div>
                                    <div class="panel-body"><h3 class="total-deposit pull-right"></h3></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="clearfix">
                                <div class="panel panel-default">
                                    <div class="panel-heading bg-blue-steel font-white">
                                        <h3 class="panel-title"><i class="fa fa-money"></i>
                                            <b>{{ __("Retirado") }}</b>
                                        </h3>
                                    </div>
                                    <div class="panel-body"><h3 class="total-withdrawal pull-right"></h3></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="clearfix">
                                <div class="panel panel-default">
                                    <div class="panel-heading bg-blue-steel font-white">
                                        <h3 class="panel-title"><i class="fa fa-money"></i>
                                            <b>{{ __("Total") }}</b>
                                        </h3>
                                    </div>
                                    <div class="panel-body"><h3 class="total-profit pull-right"></h3></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="portlet light bordered" data-load="true">
                <div class="portlet-title">
                    <div class="caption caption-md">
                        <span class="caption-subject font-blue-madison bold uppercase">{{ __('Profit') }}</span>
                    </div>
                    <div class="tools"></div>
                    <form action="" method="post" id="credit-transactions-form">
                        <div class="page-toolbar">
                            <div class="pull-right">
                                @include('layout.calendar')
                                <button class="reload btn blue" type="button" name="btn-update"
                                        id="btn-update" href="javascript:;"
                                        data-loading-text="<i class='fa fa-spin fa-spinner'></i> {{ __('Actualizando') }}">{{ __('Actualizar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="portlet-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed table-striped datatable-profit"
                               data-route="{{ route("realcasino.profitcasinos") }}">
                            <thead>
                            <tr class="font flip-content">
                                <th width="6%">{{ __('Casino') }}</th>
                                <th width="6%">{{ __('Description') }}</th>
                                <th class="no-sort" width="15%">{{ __('Depositado') }}</th>
                                <th class="no-sort" width="8%">{{ __('Retirado') }}</th>
                                <th width="8%">{{ __('Profit') }}</th>
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
@endsection

@section('scripts')
    <script src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('packages/datatables-plugins/api/fnReloadAjax.js') }}"></script>
    <script src="{{ asset('packages/jquery-validation/dist/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('packages/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('packages/jquery-datatables-columnfilter/jquery.dataTables.columnFilter.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/buttons.print.js') }}"></script>
    <script src="{{ asset('packages/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('packages/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('packages/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('packages/jquery-number/jquery.number.min.js') }}"></script>
    <script src="{{ asset('js/date-range.js') }}"></script>
    <script src="{{ asset('js/realcasino.js') }}"></script>
    <script>
        $(function () {
            RealCasino.dataTableProfitCasinos();
        });
    </script>
@endsection