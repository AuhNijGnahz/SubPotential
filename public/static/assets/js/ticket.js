$(function () {
    $('#summernote').summernote({
        height: 300,
        lang: 'zh-CN',
    });
    $('.summernote').summernote({
        height: 300,
        lang: 'zh-CN',
    });
    var rate = 0;
    $('#rate').barrating({
        theme: 'fontawesome-stars',
        showSelectedRating: false,
        onSelect: function (value) {
            rate = value;
            $('.rate').text(' ' + rate + '分');
        }
    });
    $('.br-widget').append('<span class="rate"> 0分</span>');
    $('.note-modal-footer>.text-center').remove(); //去掉编辑器的广告
    $('.note-insert').children().not(":eq(" + 0 + ")").remove(); //去掉图片和视频上传功能

    $('#ticketForm').ajaxForm(function (result) {
        var data = eval('(' + result + ')');
        if (data.status) { //创建成功
            $('#submit').attr('disable', 'true');
            toastr.success('创建工单成功！', 'Success');
            //然后要跳转到工单详情页面
        }
        else {
            toastr.error(data.message, 'Error');
        }
    });
    $('.reply').on('click', function () {
        var token = $('#token').attr('data-token');
        var content = $('.summernote').summernote('code');
        var tid = $('#tid').text();
        $.ajax({
            type: 'post',
            url: '/index/index/replyTicket',
            data: {'tid': tid, 'content': content, 'token': token},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    window.location.reload();
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        })
    })
    $('#ticket_cancel').on('click',function () {
        window.history.back();
    })
    $('#ticket_accept').on('click', function () {
        var token = $('#token').attr('data-token');
        var tid = $('#tid').text();
        $.ajax({
            type: 'post',
            url: '/index/index/acceptTicket',
            data: {'tid': tid, 'token': token},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    toastr.success('成功受理工单', 'Success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3200);
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        })
    });
    $('#ticket_rate').on('click', function () {
        var token = $('#token').attr('data-token');
        var tid = $('#tid').text();
        $.ajax({
            type: 'post',
            url: '/index/index/ticketRate',
            data: {'tid': tid, 'rate': rate, 'token': token},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    toastr.success('评价成功！工单已结束', 'Success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3200);
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        })
    });
    $('.ticket_close').on('click', function () {
        var token = $('#token').attr('data-token');
        var tid = $('#tid').text();
        $.ajax({
            type: 'post',
            url: '/index/index/closeTicket',
            data: {'tid': tid, 'token': token},
            success: function (result) {
                var data = eval('(' + result + ')');
                if (data.status) {
                    toastr.success('工单已结束，请您尽快评价', 'Success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 3200);
                }
                else {
                    toastr.error(data.message, 'Error');
                }
            }
        })
    })
})