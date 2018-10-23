$(function () {
    $('.btn_delete').on('click', function() {
        var id = $(this).val();
        $('#delete-confirm').modal({
            relatedElement: this,
            onConfirm: function() {
                $.ajax({
                    type:'POST',
                    data:{'id': id},
                    url:'/admin/webControl/dSingleAn',
                    success:function(result){
                        var obj = eval('(' + result + ')');
                        if(obj.status){
                            alert('删除公告成功！');
                            window.location.reload();
                        }
                        else{
                            alert(obj.message);
                        }
                    }
                })
            }
        });
    });
    $('#add').on('click',function () {
        window.location.href = '/admin/webControl/addAnIndex';
    })
    $('.btn_edit').on('click', function () {
        var id = $(this).val();
        window.location.href = '/admin/webControl/editAnIndex?id=' + id;
    })
    $('.btn_change').on('click', function () {
        var id = $(this).val();
        window.location.href = '/admin/webControl/changeStatus?id=' + id;
    })
})