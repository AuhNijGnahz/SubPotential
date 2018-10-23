$(function () {
    toastr.options = {
        closeButton: false,                                            // 是否显示关闭按钮，（提示框右上角关闭按钮）
        debug: false,                                                    // 是否使用deBug模式
        progressBar: true,                                            // 是否显示进度条，（设置关闭的超时时间进度条）
        onclick: null,                                                     // 点击消息框自定义事件
        showDuration: "300",                                      // 显示动画的时间
        hideDuration: "1000",                                     //  消失的动画时间
        timeOut: "3000",                                             //  自动关闭超时时间
        extendedTimeOut: "1000",                             //  加长展示时间
        showEasing: "swing",                                     //  显示时的动画缓冲方式
        hideEasing: "linear",                                       //   消失时的动画缓冲方式
        showMethod: "fadeIn",                                   //   显示时的动画方式
        hideMethod: "fadeOut"                                   //   消失时的动画方式
    };

    //首页
    $('.an_view').on('click', function () {
        var id = $(this).attr('data-id');
        $.ajax({
            type: 'post',
            data: {'id': id},
            url: '/index/index/getSingleAn',
            success: function (result) {
                var obj = eval('(' + result + ')');
                if (obj.status) {
                    $('#antitle').text(obj.anObj.title);
                    $('#ancontent').html(obj.anObj.content);
                    $('#author').text(obj.anObj.author + "  " + obj.anObj.pubdate);
                }
                else {
                    $('#antitle').text('');
                    $('#ancontent').html('<div class="alert alert-danger">\n' +
                        '                                            <h3 class="text-danger"><i class="fa fa-exclamation-triangle"></i>错误</h3>获取公告数据失败\n' +
                        '                                        </div>');
                    $('#author').text('');

                }
            }
        })
    })

    // 异步提交登录表单
    $('#loginform').ajaxForm(function (result) {
        var Obj = eval('(' + result + ')');
        if (Obj.status) {
            logincapObj.reset();
            $('#login').attr('disabled', 'disabled');
            toastr.success(Obj.message, 'Success');
            setTimeout(function () {
                window.location.href = '/index';
            }, 4000);
        }
        else {
            logincapObj.reset();
            toastr.error(Obj.message, 'Error');
        }
    });
    // 异步提交找回密码表单
    $('#recoverform').ajaxForm(function (result) {
        var Obj = eval('(' + result + ')');
        if (Obj.status) {
            $('#recover').attr('disabled', 'disabled');
            toastr.success(Obj.message, 'Success');
            setTimeout(function () {
                window.location.href = '/index';
            }, 4000);
        }
        else {
            toastr.error(Obj.message, 'Error');
        }
    });
    // 异步提交注册表单
    $('#regform').ajaxForm(function (result) {
        var Obj = eval('(' + result + ')');
        if (Obj.status) {
            regObj.reset();
            toastr.success(Obj.message, 'Success');
            setTimeout(function () {
                window.location.href = '/index/index/login';
            }, 4000);
        }
        else {
            regObj.reset();
            toastr.error(Obj.message, 'Error');
        }
    });
})