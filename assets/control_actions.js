$(document).ready(function (){
    $('body').on('click', 'a.button-empty.activate', function(e){
        e.preventDefault();
        $.get( action_for + "-activate", { id: $(this).data('id') }, function(e){location.reload(true);} );
    });
    $('body').on('click', 'a.button-empty.ban', function(e){
        e.preventDefault();
        $.get( action_for + "-ban", { id: $(this).data('id') }, function(e){location.reload(true);}  );
    });
    $('body').on('click', 'a.button-empty.fire', function(e){
        e.preventDefault();
        $.get( action_for + "-fire", { id: $(this).data('id') }, function(e){location.reload(true);}  );
    });

    $('body').on('click', 'a.button-empty.glyphicon-credit-card', function(e){
        $('#pass_change form #adminuser-id').attr('value', $(this).parent().parent().data('key'));
        $('b.pwd-username').html($(this).parent().parent().find('td:first-child').html());
    });
});