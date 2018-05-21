<div class="modal fade" id="modal-create-casino" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fa fa-times"></i></span>
                </button>
                <div class="text-rigth">
                    <div>
                        <h1 class="modal-title">{{  __('Definición de Casino') }}</h1>
                    </div>
                </div>
            </div>
            <form class="form-horizontal" action="{{ route('realcasino.create-casino') }}" method="POST"
                  enctype="multipart/form-data" id="form-create-casino">
                <div class="modal-body">
                    <div class="container">
                        <div class="form-group">
                            <label for="casino_name" class="col-md-2 control-label">{{  __('Nombre Casino') }}:</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="casino_name" id="casino_name"
                                       placeholder="{{ __('Nombre Casino') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="casino_description" class="col-md-2 control-label">{{  __('Descripción') }}
                                :</label>
                            <div class="col-md-4">
                                <textarea class="form-control" name="casino_description"
                                          id="casino_description"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="username" class="col-md-2 control-label">{{  __('Username') }}:</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="username" id="username"
                                       placeholder="{{ __('Username') }}" required>
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
                        <div class="form-group">
                            <label for="limit" class="col-md-2">{{ __('Limite de operación') }}</label>
                            <div class="col-md-4">
                                <input id="limit" name="limit" class="form-control"
                                       type="number" value="0"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-2">
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" name="typefinancial" id="percentageofprofit" value="1" checked>
                                    {{ __('Porcentaje de ganancia') }}
                                </label>
                            </div>
                            <div class="radio-inline">
                                <label>
                                    <input type="radio" name="typefinancial" id="customnetamount" value="2">
                                    {{ __('Monto definido') }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descMto" class="col-md-2">{{ __('Monto y/o Porcentaje') }}</label>
                            <div class="col-md-4">
                                <input id="descMto" name="descMto" class="form-control" data-slider-id='ex1Slider'
                                       type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1"
                                       data-slider-value="0"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{  __('Cancelar') }}
                    </button>
                    <button class="btn btn-primary btn-create"
                            data-loading-text="<i class='fa fa-spin fa-spinner'></i>">
                        {{  __('Crear') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>