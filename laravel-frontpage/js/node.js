var NodeJS;

NodeJS = function () {

    var optionsBlockUi = function (data) {
        var height = $(window).height();
        var options = {
            message: data,
            css: {
                border: 'none',
                padding: '15px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .9,
                color: '#fff',
                fontSize: '60px',
                cursor: 'none'
            },
            overlayCSS: {
                height: height,
                cursor: 'none',
                opacity: .7
            }
        }
        return options;
    };

    return {
        updateClient: function () {
            var matchID = Core.getCookie('matchID');
            var channel = 'REALCASINOCLIENT.' + matchID;
            var socket = io.connect('https://nodejs.dotworkers.com:3002');
            socket.on(channel, function (data) {
                switch (data.type) {
                    case 'updatebalance':
                        $('#balance-client').html(data.data.balance);
                        break;
                    case 'block':
                        $('#home-body').block(optionsBlockUi(data.data.message));
                        break;
                    case 'unblock':
                        $('#home-body').unblock();
                        break;
                    case 'endsession':
                        var route = $('.exit-remote').data('route');
                        $.ajax({
                            url: route,
                            type: 'get'
                        }).done(function () {
                            location.reload();
                        });
                        break;
                    case 'loginuser':
                        var route = $('.login-remote').data('route');
                        $.ajax({
                            url: route,
                            type: 'post',
                            data: {
                                user: data.data.user,
                                username: data.data.username,
                            }
                        }).done(function () {
                            location.reload();
                        });
                        break;
                    case 'chat':
                        ChatJS.message('receive', data.data.message);
                        break;
                }
            })
        },

        blockedPC: function (wl, user) {
            var routelog = 'https://nodejs.dotworkers.com:3002/userlocked/' + wl + '/' + user;
            $.get(routelog, function (data) {
                var str = data.split("/");
                if (str[0] == 'false') {
                    $('#home-body').block(optionsBlockUi(str[1]));
                } else {
                    $('#home-body').unblock();
                }
            });
        }
    }
}();