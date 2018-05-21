<div class="modal fade" id="modalTransactionsWRC" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post" id="transactionwrc-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalTitleWRC">{{ __('Retiro') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="id" class="control-label col-sm-2">{{ __('Usuario') }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="username"
                                       id="mwrc-username" placeholder="{{ __('Usuario') }}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-sm-2">{{ __('Monto Total') }}</label>
                            <input type="radio" name="amount-type" class="col-sm-2" id="total-amount" value="amounttotal" checked>
                            <div class="col-sm-8">
                              <input type="text" class="form-control" name="amount"
                                       id="mwrc-amounttotal" placeholder="{{ __('Monto') }}" readonly="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-sm-2">{{ __('Otro Monto') }}</label>
                            <input type="radio" name="amount-type" class="col-sm-2" id="other-amount">
                            <div class="col-sm-8">
                               <input type="text" class="form-control" name="amount"
                                       id="mwrc-amount" placeholder="{{ __('Monto') }}" disabled>
                            </div>
                        </div>
                        <input type="text" class="hidden" name="transaction"
                               id="mwrc-transaction" readonly>
                        <input type="text" class="hidden" name="transactiontype"
                               id="mwrc-transactiontype" readonly>
                        <input type="text" class="hidden" name="user"
                               id="mwrc-user" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger left reload"
                            id="mtwrc_rejected"
                            data-route="{{ route('realcasino.cancelledtransaction') }}" data-loading-text="<i class='fa fa-spin fa-spinner'></i>">{{ __('Rechazar') }}</button>
                    <button type="button" class="btn btn-success left reload"
                            id="mtwrc_approved"
                            data-route="{{ route('realcasino.approvedtransaction') }}" data-loading-text="<i class='fa fa-spin fa-spinner'></i>">{{ __('Aprobar') }}</button>
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('Cerrar') }}</button>
                </div>

            </form>
        </div>
    </div>
</div>
