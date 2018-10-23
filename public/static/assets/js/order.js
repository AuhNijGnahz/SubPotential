$(function () {
    function GetQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }

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

    var form = $(".validation-wizard").show();

    $(".validation-wizard").steps({
        headerTag: "h6"
        , bodyTag: "section"
        , transitionEffect: "fade"
        , titleTemplate: '<span class="step">#index#</span> #title#'
        , labels: {
            finish: "Submit"
        }
        , onStepChanging: function (event, currentIndex, newIndex) {
            if (currentIndex == 4) {
                $('div.actions').remove();
            }
            return currentIndex > newIndex || !(3 === newIndex && Number($("#age-2").val()) < 18) && (currentIndex < newIndex && (form.find(".body:eq(" + newIndex + ") label.error").remove(), form.find(".body:eq(" + newIndex + ") .error").removeClass("error")), form.validate().settings.ignore = ":disabled,:hidden", form.valid())
        }
        , onFinishing: function (event, currentIndex) {
            return form.validate().settings.ignore = ":disabled", form.valid()
        }
        , onFinished: function (event, currentIndex) {

        }
    }), $(".validation-wizard").validate({
        ignore: "input[type=hidden]"
        , errorClass: "text-danger"
        , successClass: "text-success"
        , highlight: function (element, errorClass) {
            $(element).removeClass(errorClass)
        }
        , unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass)
        }
        , errorPlacement: function (error, element) {
            error.insertAfter(element)
        }
        , rules: {
            email: {
                email: !0
            }
        }
    })

    var mainprice = 0;
    var discount = 1; // 优惠码折扣额度
    var coupon = "";
    var pid = 0;
    var method = null;
    var pname = "";
    var percent;
    var sid = GetQueryString('sid');
    var type = GetQueryString('type');
    var renewid = GetQueryString('renewid');
    if(groupdiscount < 1){
        percent = toDecimal2(1 - parseFloat(groupdiscount)) * 100;
        $('#dis').before("<span class='label label-light-success'>用户组优惠 (-" + percent + "%)</span>");
        discount *= groupdiscount;
    }

    if (type === 'renew' && renewdiscount < 1) {
        percent = toDecimal2(1 - parseFloat(renewdiscount)) * 100;
        $('#dis').before("<span class='label label-light-success'>续费优惠 (-" + percent + "%)</span>");
        discount *= renewdiscount;
    }

    $("#print").click(function () {
        var mode = 'iframe'; //popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("div.printableArea").printArea(options);
    });

    $('.price').on('click', function () {
        pid = $(this).attr('data-id');
        $.ajax({
            type: 'post',
            data: {'pid': pid},
            url: '/index/index/getPrice',
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    method = data.price[0].method;
                    pname = data.price[0].pname;
                    $('#pname').text(pname);
                    if (method === '余额') {
                        mainprice = data.price[0].price;
                        setPrice(mainprice, discount, method);
                        // toastr.success('获取价格成功！', 'Success');
                    }
                    else if (method === '积分') {
                        mainprice = data.price[0].price;
                        setPrice(mainprice, discount, method);
                        // toastr.success('获取价格成功！', 'Success');
                    }
                }
                else {
                    toastr.error('获取价格失败,请刷新页面！', 'Error');
                }
            }
        })
    })
    $('#couponcheck').on('click', function () {
        coupon = $('#coupon').val();
        // $(this).attr('disabled', 'disabled').html('<i class="am-icon-spinner am-icon-circle-o-notch"></i> 处理中');
        if (coupon === null || coupon.length < 4 || coupon === '') {
            toastr.error('请输入正确的优惠代码', 'Error');
            // $(this).removeAttr('disabled').html('<i class="am-icon-gg"></i> 确认');
        }
        else {
            $.ajax({
                type: 'post',
                url: '/index/index/checkCoupon',
                data: {'coupon': coupon, 'sid': sid},
                success: function (result) {
                    var data = eval('(' + result + ')');
                    if (data.status) {
                        discount *= data.cpObj[0].discount; //折扣要相加
                        setPrice(mainprice, discount, method);
                        $('#coupon').attr('disabled', 'disabled');
                        var percent = toDecimal2(1 - parseFloat(data.cpObj[0].discount)) * 100;
                        $('#couponcheck').hide().parent().parent().empty().append('<div class="alert alert-success">\n' +
                            '                                                    <h3 class="text-success"><i class="fa fa-check-circle"></i> 提醒您</h3>\n' +
                            '                                                    您使用优惠代码获得了' + toDecimal2(data.cpObj[0].discount * 10) + '折优惠</div>');
                        $('#dis').before('<span class=\'label label-light-success\'>优惠代码 (-' + percent + '%)</span>\n');
                        toastr.success('优惠代码应用成功！', 'Success');
                    }
                    else {
                        toastr.error(data.message, 'Error');
                        // $('#couponcheck').removeAttr('disabled').html('<i class="am-icon-gg"></i> 确认');
                    }
                }
            })
        }
    })

    function setPrice(mainprice, discount, method) {
        var disprice = mainprice * discount;
        if (method === '余额') {
            $('.mainprice').html("<i class=\"fa fa-cny\"></i> " + mainprice.toFixed(2));
            $('#disprice').html("<i class=\"fa fa-cny\"></i> " + disprice.toFixed(2));
        }
        else if (method === '积分') {
            $('.mainprice').html("<i class=\"fa fa-diamond\"></i> " + mainprice.toFixed(2));
            $('#disprice').html("<i class=\"fa fa-diamond\"></i> " + disprice.toFixed(2));
        }
    }

    $('#purchase').on('click', function () {
        var $btn = $(this);
        if (pid === 0) {
            toastr.error('不合法的价格！', 'Error');
        }
        else {
            $btn.attr('disabled', 'disabled');
            $.ajax({
                type: 'post',
                url: '/index/index/purchase',
                data: {
                    'sid': sid,
                    'pid': pid,
                    'coupon': coupon,
                    'type': type,
                    'renewid': renewid
                },
                success: function (result) {
                    var data = eval('(' + result + ')');
                    if (data.status) {
                        swal({
                            title: "恭喜您",
                            text: "您已经成功完成购买，您可以到我的订阅栏目查看您刚刚订阅的产品！",
                            type: "success",
                            showCancelButton: false,
                            confirmButtonColor: "#00EE00",
                            confirmButtonText: "查看我的订阅",
                            closeOnConfirm: false
                        }, function () {
                            window.location.href = '/index/index/mySubscription';
                        })
                    }
                    else {
                        swal({
                            title: "很抱歉",
                            text: "由于" + data.message + " 您未能完成此次购买！",
                            type: "error",
                            showCancelButton: false,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "返回",
                            closeOnConfirm: false
                        }, function () {
                            window.location.reload();
                        })
                    }
                }
            })
        }
    })
})