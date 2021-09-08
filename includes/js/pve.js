function getPokemonMoves(pokemon) {
    pokemon = pokemon.replaceAll(' ', '_');

    $.ajax({
        type: "GET",
        url: "getPokemon/" + pokemon,
        success: function(data){
            data = jQuery.parseJSON(data)
            $.each(data.moveset.quick, function (index,value){
                $('#quick_move-pkmpve').append("<option>" + value + "</option>")
            });
            $.each(data.moveset.charge, function (index,value){
                $('#charge1_move-pkmpve').append("<option>" + value + "</option>")
                $('#charge2_move-pkmpve').append("<option>" + value + "</option>")
            });
        }
    });
}

$(document).on('click', '.pkm-pve-row',function() {
    $('.edit-btn').removeAttr('disabled');
    $('.delete-btn').removeAttr('disabled');
    $('#idpkmpvedel').val($(this).closest('tr').attr('id'));
    $('#idpkmpveedt').val($(this).closest('tr').attr('id'));
});

$(document).on('click', '.add-btn', function() {
    $('#modalPkmPveAdd').modal('show');
    $('#idpkmpve').val($('.store').attr('id'));
});

$(document).on('click', '.delete-btn', function() {
    $('#modalPkmPveDel').modal('show');
});

$(document).on('click', '.edit-btn', function() {
    $.ajax({
        type: 'GET',
        url: 'pkmpve/' + $("#idpkmpvedel").val(),
        complete: function (response) {
            response = jQuery.parseJSON(response.responseText)
            $("#name_edt").val(response.name);
            $("#cp_edt").val(response.cp);
            $("#lv_edt").val(response.lv);
            $("#atk_iv_edt").val(response.atk_iv);
            $("#def_iv_edt").val(response.def_iv);
            $("#sta_iv_edt").val(response.sta_iv);
            $("#percentage_iv_edt").val(response.iv_percentage);
            $("#role_edt").val(response.role);
        }
    });

    $('#modalPkmPveEdt').modal('show');
});

$(document).on('click', '.save-btn', function() {
    /*$( "#idpkmpve-form input, #idpkmpve-form select" ).each(function(){
        console.log($(this).attr('id') + " " + $(this).attr('required') + " " + $(this).val());
    }); */

    $.ajax({
        type: 'POST',
        url: 'pkmpve',
        data: $("#idpkmpve-form").serialize(),
        complete: function (response) {
            response = jQuery.parseJSON(response.responseText)
            $(".store")
                .before(response.newRow)
                .attr('id', response.nextId);
            $("#idpkmpve").val(response.nextId);
        }
    });
});

$(document).on('click', '.edt-btn', function() {
    $.ajax({
        type: 'POST',
        url: 'pkmpve/' + $("#idpkmpveedt").val(),
        data: $("#idpkmpveedt-form").serialize(),
        complete: function (response) {
            $('#pkm-pve-rows tr').each(function(){
                if ($(this).attr('id') == $("#idpkmpveedt").val()){
                    $(this)
                        .before(response.responseText)
                        .remove();
                }
            });
            $('#modalPkmPveEdt').modal('hide');
            $("#idpkmpveedt").val('');
        }
    });
});

$(document).on('click', '.del-btn', function() {
    $.ajax({
        type: 'DELETE',
        url: 'pkmpve/' + $("#idpkmpvedel").val(),
        complete: function () {
            $('#modalPkmPveDel').modal('hide');
            $('#pkm-pve-rows tr').each(function(){
                if ($(this).attr('id') == $("#idpkmpvedel").val()){
                    $(this).remove();
                }
            });
            $("#idpkmpvedel").val('');
        }
    });
});
