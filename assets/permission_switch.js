$(function(){
    $.fn.bootstrapSwitch.defaults.size = 'mini';
    var initSwitch = function (event, state) {
        var permission = $(this).parents('tr').find('td.permission-name').html();
        var enabled;
        if(state==true){
            enabled = 1;
        } else {
            enabled = 0;
        }
        $.post(
            "change",
            {
                permission: permission,
                enabled: enabled,
                user_id: user_id
            }
        );
    };

    $("[name='my-checkbox']").bootstrapSwitch();
    $("[name='my-checkbox']").on('switchChange.bootstrapSwitch', initSwitch);

    $( document ).ajaxComplete(function() {
        $("[name='my-checkbox']").bootstrapSwitch();
        $("[name='my-checkbox']").unbind('switchChange');
        $("[name='my-checkbox']").on('switchChange.bootstrapSwitch', initSwitch);
    });
});

