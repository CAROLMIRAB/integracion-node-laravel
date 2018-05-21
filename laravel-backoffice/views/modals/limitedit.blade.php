<div class="modal fade" id="modalEditLimit" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post" id="edit-limit">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalEditLabel">{{ __('Limite') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="name" class="control-label col-sm-2">{{ __('Monto') }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="amount"
                                       id="amount-limit" placeholder="{{ __('Monto') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-success left btn-accept"
                        id="btn-accept"
                        data-route="{{ route('realcasino.edit-limit',[$casino->id]) }}">{{ __('Aceptar') }}</button>
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>
</div>
