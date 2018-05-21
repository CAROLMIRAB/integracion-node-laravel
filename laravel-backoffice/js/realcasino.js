var RealCasino;

RealCasino = function () {

    var optionsBlockUi = function () {
        var height = $(window).height();
        var options = {
            overlayCSS: {
                height: height,
                cursor: 'none',
                opacity: .7
            }
        }
        return options;
    };

    return {
        sendMessage: function () {
            var route = $('#chatjs').data('route');
            $(document).on('click', '#btn-chat', function () {
                var message = $('.chat_input').val();
                $.ajax({
                    url: route,
                    type: 'post',
                    data: {
                        message: message
                    }
                }).done(function () {
                    ChatJS.message('sent', message);
                });
            });

            $('.chat_input').keypress(function (e) {
                if (e.which == 13) {
                    var message = $(this).val();
                    $.ajax({
                        url: route,
                        type: 'post',
                        data: {
                            message: message
                        }
                    }).done(function () {
                        ChatJS.message('sent', message);
                    });
                }
            });

        },

        requestDeposit: function () {
            var route = $('.deposit').data('route');
            var message = null;
            $('.deposit').on('click', function () {
                swal({
                        title: '¿Desea hacer un depósito?',
                        text: 'Confirme si desea realizar la operación',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        confirmButtonText: 'Si',
                        showLoaderOnConfirm: true,
                        preConfirm: function () {
                            return new Promise(function (resolve, reject) {
                                $.ajax({
                                    url: route,
                                    type: 'post',
                                    dataType: 'json',
                                    data: {
                                        match: Core.getCookie('matchID')
                                    }
                                }).done(function (json) {
                                    message = json.message;
                                    switch (json.status) {
                                        case "SUCCESS":
                                            resolve();
                                            sa_type = 'success';
                                            break;
                                        case "ERROR":
                                            resolve();
                                            sa_type = 'warning';
                                            break;
                                    }
                                }).fail(function (error) {
                                    reject(error.message);
                                    sa_type = 'error';
                                });
                            })
                        },
                        allowOutsideClick: false
                    }
                ).then(function () {
                    swal({
                        type: sa_type,
                        text: message
                    }).then(function () {

                    }).catch();

                }).catch()
            })
        },

        requestWithdrawal: function () {
            var route = $('.withdrawal').data('route');
            var message = null;
            $('.withdrawal').on('click', function () {
                swal({
                        title: '¿Desea hacer un retiro?',
                        text: 'Confirme si desea realizar la operación',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        confirmButtonText: 'Si',
                        showLoaderOnConfirm: true,
                        preConfirm: function () {
                            return new Promise(function (resolve, reject) {
                                $.ajax({
                                    url: route,
                                    type: 'post',
                                    dataType: 'json',
                                    data: {
                                        match: Core.getCookie('matchID')
                                    }
                                }).done(function (json) {
                                    message = json.message;
                                    switch (json.status) {
                                        case "SUCCESS":
                                            resolve();
                                            sa_type = 'success';
                                            break;
                                        case "ERROR":
                                            resolve();
                                            sa_type = 'warning';
                                            break;
                                    }
                                }).fail(function (error) {
                                    reject(error.message);
                                    sa_type = 'error';
                                });
                            })
                        },
                        allowOutsideClick: false
                    }
                ).then(function () {
                    swal({
                        type: sa_type,
                        text: message
                    }).then(function () {

                    }).catch();

                }).catch()
            })
        },

        match: function (id, user) {
            $(document).on('click', '.match-button', function () {
                var nodekey = id + user;
                Core.setCookie('matchID', nodekey, 730);
                $('.match-div').html('');
                location.reload();
            })
        },

        matchAppend: function (id, user) {
            var nodekey = id + user;
            if (Core.getCookie('matchID') == '' || Core.getCookie('matchID') != nodekey) {
                $('.match-div').append(' <a class="btn-mega pull-left btn-conect btn-ms-theme-02 match-button" href="#"><i class="fa fa-plug"></i> CONNECT</a>');
            }
        },

        backUpload: function(){
            $(window).on("navigate", function (event, data) {
                var direction = data.state.direction;
                if (direction == 'back') {
                    location.reload();
                }
                if (direction == 'forward') {
                    // do something else
                }
            });
        }
    }
}();