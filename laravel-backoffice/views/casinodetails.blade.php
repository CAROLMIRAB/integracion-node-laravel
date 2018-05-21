@extends('template')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/multiselect/css/multi-select.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet"
          href="{{ asset('packages/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/datatables-buttons/css/buttons.dataTables.scss') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/other.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/datatable-loader.css') }}"/>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <!-- BEGIN PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-users"></i><span
                                class="caption-subject font-blue-madison bold uppercase">{{  __('Detalles del Casino') }}</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse">
                        </a>
                    </div>
                </div>
                <div class="portlet-body form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-3 ">
                                {{  __('Usuario') }}:
                            </label>
                            <div class="col-md-8">
                                {{ $casino->username }}
                            </div>
                            <div class="col-md-1">

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">
                                {{  __('Descripción') }}:
                            </label>
                            <div class="col-md-7" id="description">
                                {{ $casino->description }}
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-success fa fa-pencil" data-toggle="#modalEditDescription" id="btn-description"></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">
                                {{  __('Limite Operación') }}:
                            </label>
                            <div class="col-md-7" id="limitAmount">{{ $casino->operationlimit }}</div>
                            <div class="col-md-2">
                                <button class="btn btn-success fa fa-pencil" data-toggle="#modalEditLimit" id="btn-limit"></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">
                                {{  __('Fecha de creación') }}:
                            </label>
                            <div class="col-md-8">
                                {{ $casino->created_at }}
                            </div>
                            <div class="col-md-1">

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3">
                                {{  __('Limite restante') }}:
                            </label>
                            <div class="col-md-8">
                                {{ $limit }}
                            </div>
                            <div class="col-md-1">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-users"></i><span
                                class="caption-subject font-blue-madison bold uppercase">{{  __('Usuarios asociados al casino') }}</span>
                    </div>
                    <div class="tools">

                    </div>
                </div>
                <div class="portlet-body form">
                    <div class="form-send" data-route="{{ route('realcasino.add-user-casino') }}">
                        <div class="form-body">
                            <div class="form-group" id="dataUsers">
                                <div class="controls controls-to">
                                    <div class="row">
                                        <label class="search-label col-md-2"
                                               for="typeTransaction">{{ __('Usuario') }}: </label>
                                        <div class="col-md-8">
                                            <select class="form-control" id="user-search"
                                                    data-route="{{ route('searchUsers') }}"
                                                    name="users[]" multiple="multiple">
                                            </select>
                                             <span class="help-block">
                                                 {{  __('Los usuarios colocados como tags o etiquetas son los pertenecientes al grupo.') }}
                                             </span>
                                        </div>
                                        <div class="col-md-2">
                                              <span class="input-group-btn">
                                                    <button type="button" class="btn green btn-update"><i
                                                                class="fa fa-plus"></i> {{  __('Agregar') }}
                                                    </button>
                                              </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden">
                                <input type="text" class="form-control" name="casino" id="casino"
                                       value="{{ $casino->id }}"/>
                            </div>
                        </div>

                    </div>
                    <table class="table table-bordered table-condensed table-striped datatable-transactions"
                           id="casinousers"
                           data-route="{{ route('realcasino.get-detail-casino-by-id',[$casino->id]) }}">
                        <thead>
                        <tr>
                            <th width="20%">
                                {{  __('ID') }}
                            </th>
                            <th width="20%">
                                {{  __('Usuario') }}
                            </th>
                            <th width="20%">
                                {{  __('Estatus') }}
                            </th>
                            <th width="5%" class="no-sort">

                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('realcasino.modals.limitedit')
    @include('realcasino.modals.descriptionedit')
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('packages/jquery-datatables-columnfilter/jquery.dataTables.columnFilter.js') }}"></script>
    <script src="{{ asset('packages/datatables-plugins/api/fnReloadAjax.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('packages/datatables-buttons/js/buttons.print.js') }}"></script>
    <script src="{{ asset('packages/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('packages/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('packages/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('js/realcasino.js') }}"></script>
    <script>
        $(function () {
            RealCasino.initUsersSearch();
            RealCasino.usersCasinoDataTable();
            RealCasino.detailsCasinoEdit();
        })
    </script>
@endsection
