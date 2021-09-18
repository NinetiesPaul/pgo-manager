function disableMove(value, target) {
    $('#' + target + " option").each(function(){
        if ($(this).val() == value) {
            $(this).attr('disabled', 'disabled')
        } else {
            $(this).removeAttr('disabled')
        }
    });
}

function getMoveData(move, type, target) {
    move = move.replaceAll(' ', '_');
    move = move.replaceAll('*', '');

    $.ajax({
        type: "GET",
        url: "getMove/" + move + "/" + type,
        success: function(data){

            data = jQuery.parseJSON(data)

            imgSrc = 'https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Types/POKEMON_TYPE_' + data.type + '.png';
            $('#' + type + '_move_type-' + target).html("<img src='" + imgSrc + "' height='25px' width='25px'/>");

            $('#' + type + '_goodAgainst-' + target).html('');
            $('#' + type + '_goodAgainst-' + target).css('display', 'revert');
            $('#' + type + '_weakAgainst-' + target).html('');
            $('#' + type + '_weakAgainst-' + target).css('display', 'revert');

            $.each(data.goodAgainst, function (index,value){

                $('#' + type + '_goodAgainst-' + target).append(value);

                if (index != data.goodAgainst.length - 1) {
                    $('#' + type + '_goodAgainst-' + target).append(" | ");
                }
            });

            $.each(data.weakAgainst, function (index,value){
                $('#' + type + '_weakAgainst-' + target).append(value);

                if (index != data.weakAgainst.length - 1) {
                    $('#' + type + '_weakAgainst-' + target).append(" | ");
                }
            });
        }
    });
}

$(document).on('change', '#quick_move-slot1', function() {
    getMoveData(this.value, 'quick', 'slot1');
});

$(document).on('change', '#charge1_move-slot1', function() {
    disableMove(this.value, 'charge2_move-slot1');
    getMoveData(this.value, 'charge1', 'slot1');
});

$(document).on('change', '#charge2_move-slot1', function() {
    disableMove(this.value, 'charge1_move-slot1');
    getMoveData(this.value, 'charge2', 'slot1');
});

$(document).on('change', '#quick_move-slot2', function() {
    getMoveData(this.value, 'quick', 'slot2');
});

$(document).on('change', '#charge1_move-slot2', function() {
    disableMove(this.value, 'charge2_move-slot2');
    getMoveData(this.value, 'charge1', 'slot2');
});

$(document).on('change', '#charge2_move-slot2', function() {
    disableMove(this.value, 'charge1_move-slot2');
    getMoveData(this.value, 'charge2', 'slot2');
});

$(document).on('change', '#quick_move-slot3', function() {
    getMoveData(this.value, 'quick', 'slot3');
});

$(document).on('change', '#charge1_move-slot3', function() {
    disableMove(this.value, 'charge2_move-slot3');
    getMoveData(this.value, 'charge1', 'slot3');
});

$(document).on('change', '#charge2_move-slot3', function() {
    disableMove(this.value, 'charge1_move-slot3');
    getMoveData(this.value, 'charge2', 'slot3');
});

$(document).on('click', '.pkm-list-btn', function() {
    $.ajax({
        type: "POST",
        url: "teamBuilder",
        data: { 'pkm-list' : $(".pkm-list").val() },
        success: function(response){

            $(".teamassembler-table").html("<thead class='thead-dark'><tr><th>#</th><th>Slot 1</th><th>Slot 2</th><th>Slot 3</th><th>Resistances</th><th>Vulnerabilities</th></tr></thead>");

            response = jQuery.parseJSON(response);

            var rowText = '<tbody>';

            $.each(response, function (index,value){

                rowText += '<tr><td>' + (parseInt(index) + 1) + '</td>';

                $.each(value.members, function (index,value){
                    imgTag = '';
                    /*
                    imageSrc = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/" + value.imgUrl + ".png";
                    imgTag = "<img src='" + imageSrc + "\' heigth='25%' width='25%' />";
                    */

                    rowText += '<td>'+imgTag+'<b>' + value.name + '</b><br><small>' + value.type.join('/') + '</small></td>';
                });

                rowText += '<td>' + value.resistances + '</td><td>' + value.weaknesses + '</td></tr>';

            });

            rowText += '</tbody>';

            $(".teamassembler-table").append(rowText);

        }
    });
});

