$(function () {
    var TimerId = 0;
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        width: 236,
        height: 236
    });

    function toDecimal2(x) {
        var f = parseFloat(x);
        if (isNaN(f)) {
            return false;
        }
        var f = Math.round(x * 100) / 100;
        var s = f.toString();
        var rs = s.indexOf('.');
        if (rs < 0) {
            rs = s.length;
            s += '.';
        }
        while (s.length <= rs + 2) {
            s += '0';
        }
        return s;
    }

    function checkOrder(orderid) {
        $.ajax({
            type: 'get',
            url: '/index/index/checkOrder',
            data: {'orderid': orderid},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    $('#qrcode').hide();
                    $('#ccontent').text('恭喜您，支付成功！');
                    $('#success').fadeIn(1500);
                }
            }
        })
    }

    $('#normalcharge').on('click', function () {
        var methodid = $('.method:checked').val();
        var $btn = $(this);
        var chargecount = $('#chargecount').val();
        if (!$('.method').is(':checked')) {
            toastr.warning('支付方式非法！', 'Warning');
        }
        else if (chargecount.length === 0) {
            toastr.warning('充值金额非法！', 'Warning');
        }
        else {
            // 进入充值进程
            $btn.attr('disabled', 'true').html('<i class="fa fa-refresh fa-spin"></i>' + ' 处理中');
            setTimeout(function () {
                $.ajax({
                    type: 'post',
                    url: '/index/index/chargeDo',
                    data: {
                        'chargecount': chargecount,
                        'type': 'normal',
                        'methodid': methodid
                    },
                    success: function (result) {
                        var data = eval('(' + result + ')');
                        if (data.status) {
                            if (data.method === 'youzan') {
                                qrcode.makeCode(data.qrurl);
                                $('#chargemodel').modal();
                                $('#ctitle').text('扫码支付（为UID：' + data.uid + ' 充值' + '）');
                                $('#csubtitle').text('￥' + toDecimal2(chargecount));
                                $('#ccontent').html('<br/>使用手机扫描二维码完成支付！<br/><i class="fa fa-check"></i> 支付宝 <i class="fa fa-check"></i> 微信 <i class="fa fa-check"></i> 财付通 <i class="fa fa-check"></i> 银联');
                                TimerId = window.setInterval(function () {
                                    checkOrder(data.qrid);
                                }, 3000)
                                // var url = data.qru6rl;
                                // alert(url);
                            }
                        }
                    }
                })
            }, 500)
        }
        // $('#chargemodel').modal();
    })

    $('#cardcharge').on('click', function () {
        var card = $('#card').val();
        if (card.length !== 20) {
            toastr.error('序列号格式错误！', 'Error');
        }
        else {
            $.ajax({
                type: 'post',
                url: '/index/index/chargeDo',
                data: {
                    'type': 'card',
                    'card': card
                },
                success: function (result) {
                    var data = eval('(' + result + ')');
                    if (data.status) {
                        toastr.success('序列号应用成功！', 'Success');
                    }
                    else {
                        toastr.error(data.message, 'Error');
                    }
                }
            })
        }
    })
})