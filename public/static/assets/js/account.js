$(function () {
    // Translated
    $('.dropify').dropify({
        messages: {
            default: '点击上传或直接把文件拖进来',
            replace: '点击重新选择或直接把文件拖进来',
            remove: '不要了',
            error: '这个文件太大了没办法上传啊！'
        }
    });
    // Used events
    var drEvent = $('#input-file-events').dropify();

    drEvent.on('dropify.beforeClear', function (event, element) {
        return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
    });

    drEvent.on('dropify.afterClear', function (event, element) {
        alert('File deleted');
    });

    drEvent.on('dropify.errors', function (event, element) {
        console.log('Has Errors');
    });

    var drDestroy = $('#input-file-to-destroy').dropify();
    drDestroy = drDestroy.data('dropify')
    $('#toggleDropify').on('click', function (e) {
        e.preventDefault();
        if (drDestroy.isDropified()) {
            drDestroy.destroy();
        } else {
            drDestroy.init();
        }
    })

    $('#changeusername').on('click', function () {
        $('#changeusernamemodel').modal();
    });
    $('#CUN').on('click', function () {
        var key = /^[a-zA-Z0-9_-]{4,16}$/;
        var newname = $('#username').val();
        if (newname.length < 6) {
            swal({
                type: 'error',
                title: '错误',
                text: '您的新用户名太短了！',
                confirmButtonText: '确认'
            });
        }
        else if (!key.test(newname)) {
            swal({
                type: 'error',
                title: '错误',
                text: '用户名只能由字母和数字构成哦！',
                confirmButtonText: '确认'
            });
        }
        else {
            swal({
                title: '您确认吗？',
                text: '您将消费￥' + cprice + ' 来更改1次您的用户名！',
                type: 'info',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '确认',
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function () {
                setTimeout(function () {
                    $.ajax({
                        type: 'post',
                        url: '/index/user/changeUserName',
                        data: {'username': newname},
                        success: function (result) {
                            var Obj = eval('(' + result + ')');
                            if (Obj.status) {
                                swal({
                                    type: 'success',
                                    title: '修改成功',
                                    text: '您成功更改了您的用户名！',
                                    confirmButtonText: '确认'
                                });
                            }
                            else {
                                swal({
                                    type: 'error',
                                    title: '修改失败',
                                    text: '修改失败，' + Obj.message + ' !',
                                    confirmButtonText: '确认'
                                });
                            }
                        }
                    })
                }, 1500);
            })
        }
    })

    $('#deleteacc').on('click', function () {
        swal({
            title: '您确认吗？',
            text: '这项操作是非常危险且不能撤销的，一旦您确认，您的账户以及账户内的所有资料将会被彻底删除(包括您的余额，订阅，购买记录等一切信息且无论它们是否已经消费完或者到期)，请三思！如果您确认删除，请在接下来出现的输入框内输入您的登录密码以最后一次验证您的身份。',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: '取消',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#4EEE94',
            confirmButtonText: '我确认删除',
            closeOnConfirm: false
        }, function () {
            swal({
                    title: "最终身份确认",
                    text: "请输入您的登录密码",
                    type: "input",
                    inputType: 'password',
                    showCancelButton: true,
                    closeOnConfirm: false,
                    inputPlaceholder: "输入您的登录密码",
                    showLoaderOnConfirm: true
                },
                function (inputValue) {
                    if (inputValue === false) {
                        return false;
                    }
                    if (inputValue === "") {
                        swal.showInputError("请输入您的登录密码！");
                        return false
                    }
                    setTimeout(function () {
                        $.ajax({
                            type: 'post',
                            url: '/index/user/deleteMyAcc',
                            data: {'password': inputValue},
                            success: function (result) {
                                var Obj = eval('(' + result + ')');
                                if (Obj.status) {
                                    swal({
                                        type: 'success',
                                        title: '成功',
                                        text: '操作已完成，您的账户已经被彻底删除，在您按下确认按钮后将引导您返回到登录页面，感谢您对本站的支持，再见！',
                                        confirmButtonText: '确认'
                                    }, function () {
                                        window.location.href = '/index/index/login'
                                    });
                                }
                                else {
                                    swal({
                                        type: 'error',
                                        title: '错误',
                                        text: '操作无法完成，您的登录密码验证失败！',
                                        confirmButtonText: '确认'
                                    });
                                }
                            }
                        });
                    }, 2000)
                });
        });
    });
    $('#changepwd').on('click', function () {
        $('#changepwdmodel').modal();
    })
    $('#cPwdForm').ajaxForm(function (result) {
        var data = eval('(' + result + ')');
        if (data.status) {
            toastr.success('修改密码成功！', 'Success');
            setTimeout(function () {
                window.location.href = '/index/index/login';
            }, 3500);
        }
        else {
            toastr.error(data.message, 'Error');
        }
    });

    $('#cAvatarForm').ajaxForm(function (result) {
        var data = eval('(' + result + ')');
        if (data.status) {
            toastr.success('修改头像成功！', 'Success');
        }
        else {
            toastr.error(data.message, 'Error');
        }
    });
    $('.verifyemail').on('click', function () {
        $(this).html('<i class="fa fa-exclamation-circle text-warning"> 发送中</i>');
        $.ajax({
            type: 'post',
            url: '/index/Active/verifyEmail',
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    $('.verifyemail').html('<i class="fa fa-exclamation-circle text-warning"> 邮件已发送</i>');
                    toastr.success('成功发送激活邮件！', 'Success');
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        });
    })
    $('#changemail').on('click', function () {
        $('#changemailmodel').modal();
    })
});