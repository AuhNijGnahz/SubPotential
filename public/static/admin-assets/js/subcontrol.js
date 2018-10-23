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

    $('.btn_change').on('click',function () {
        var sid = $(this).attr('data-sid');
        $.ajax({
            type: 'post',
            url: '/admin/SubControl/changeStatus',
            data: {
                'sid': sid
            },
            success: function (result) {
                var Obj = eval('(' + result + ")");
                if (Obj.status) {
                    toastr.success('更改状态成功', 'Success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3500)
                }
                else {
                    toastr.error(Obj.message, 'Success');
                }
            }
        })
    })

    $('.btn_delete').on('click', function () {
        var sid = $(this).val();
        $.ajax({
            type: 'post',
            url: '/admin/SubControl/dSingleSub',
            data: {
                'sid': sid
            },
            success: function (result) {
                var Obj = eval('(' + result + ")");
                if (Obj.status) {
                    toastr.success('删除成功', 'Success');
                    setTimeout(function () {
                        window.location.reload()
                    }, 3500)
                }
                else {
                    toastr.error(Obj.message, 'Success');
                }
            }
        })
    })
    $('.btn_plan').on('click',function () {
        window.location.href='/admin/subControl/subPrice';
    })
    $('.btn_edit').on('click',function () {
        var sid = $(this).val();
        window.location.href='/admin/subControl/editSubIndex?sid='+sid;
    })
    $('#add').on('click', function () {
        window.location.href = '/admin/subControl/addSubIndex';
    })

    // 生成秘钥
    var skey = randomWord(false, 36);
    if($('#skey').val()===""){
        $('#skey').val(skey);
    }
    $('#changeskey').click(function () {
        $(this).attr('disabled', 'disabled');
        skey = randomWord(false, 36);
        $('#skey').val(skey);
        toastr.success('更换Secret Key成功', 'Success');
        setTimeout(function () {
            $('#changeskey').removeAttr('disabled');
        }, 3000);
    })


})