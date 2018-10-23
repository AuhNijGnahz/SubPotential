$(function () {
    $('.btn_bindgroup').on('click', function () {
        $('#bindGroup').modal();
    })
    $('#bindnow').on('click', function () {
        var groupnum = $('#groupnum').val();
        var id = $(this).attr('data-id');
        if (groupnum.length < 5) {
            toastr.error('群号码格式错误！', 'Error');
        }
        else {
            $.ajax({
                type: 'post',
                url: "/index/index/bindGroup",
                data: {'id': id, 'groupnum': groupnum},
                success: function (result) {
                    var data = eval('(' + result + ')');
                    if (data.status) {
                        $('#bindnow').hide(500);
                        $('#bindsuccess').show(500);
                    }
                    else {
                        toastr.error(data.message, 'Error');
                    }
                }
            })
        }
    })
})