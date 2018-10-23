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
    $('.btn_delete').on('click', function () {
        var uid = $(this).val();
        $('#user-delete-confirm').modal({
            relatedElement: this,
            onConfirm: function () {
                $.ajax({
                    type: 'POST',
                    data: {'uid': uid},
                    url: '/admin/UserControl/dSingleUser',
                    success: function (result) {
                        var obj = eval('(' + result + ')');
                        if (obj.status) {
                            toastr.success('删除用户成功！', 'Success');
                            window.location.reload();
                        }
                        else {
                            toastr.error('删除用户失败', 'Error');
                        }
                    }
                })
            }
        });
    });
    $('.btn_edit').on('click', function () {
        var uid = $(this).val();
        window.location.href = '/admin/UserControl/editUserIndex?uid=' + uid;
    })
    $('#adduser').on('click', function () {
        window.location.href = '/admin/UserControl/addUserIndex';
    })
    $('.btn_change').on('click', function () {
        var id = $(this).val();
        $.ajax({
            type: 'post',
            url: '/admin/UserControl/changeUserSubStatus',
            data: {'id': id},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    toastr.success('修改状态成功！', 'Success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3500)
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        })
    })
    $('.btn_editgroup').on('click', function () {
        var gname = $(this).attr('data-gname');
        var gcolor = $(this).attr('data-gcolor');
        var gdis = $(this).attr('data-gdis');
        var price = $(this).attr('data-price');
        $('#gname').val(gname);
        $('#color').val(gcolor);
        $('#discount').val(gdis);
        $('#price').val(price);
    })
    $('.btn_deletegroup').on('click', function () {
        var id = $(this).val();
        if(confirm("您确认删除这个用户组吗？") === true){
            $.ajax({
                type: 'POST',
                data: {'id': id},
                url: '/admin/UserControl/deleteUserGroup',
                success: function (result) {
                    var obj = eval('(' + result + ')');
                    if (obj.status) {
                        toastr.success('成功删除用户组！', 'Success');
                        window.location.reload();
                    }
                    else {
                        toastr.error(obj.message, 'Error');
                    }
                }
            })
        }
    });
});