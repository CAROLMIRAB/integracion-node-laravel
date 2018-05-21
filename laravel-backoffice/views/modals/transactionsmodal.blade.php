<div class="modal fade" id="modalTransactionsRC" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post" id="alltransactionrc-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalTitleDRC">{{ __('Depósito') }}</h4>
                    <h4 class="modal-title" id="modalTitleWRC">{{ __('Retiro') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="id" class="control-label col-sm-2">{{ __('Usuario') }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="username"
                                       id="mt-username" placeholder="{{ __('Usuario') }}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-sm-2">{{ __('Monto') }}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="amount"
                                       id="mt-amount" placeholder="{{ __('Monto') }}">
                            </div>
                        </div>
                        <input type="text" class="hidden" name="transaction"
                               id="mt-transaction" readonly>
                        <input type="text" class="hidden" name="transactiontype"
                               id="mt-transactiontype" readonly>
                        <input type="text" class="hidden" name="user"
                               id="mt-user" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger left"
                            id="mtrc_rejected"
                            data-route="{{ route('realcasino.cancelledtransaction') }}">{{ __('Rechazar') }}</button>
                    <button type="button" class="btn btn-success left"
                            id="mtrc_approved"
                            data-route="{{ route('realcasino.approvedtransaction') }}">{{ __('Aprobar') }}</button>
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('Cerrar') }}</button>
                </div>

            </form>
        </div>
    </div>
</div>
