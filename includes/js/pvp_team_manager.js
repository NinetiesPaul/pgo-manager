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
    move = move.replace(' ', '_');

    $.ajax({
        type: "GET",
        url: "getMove/" + move + "/" + type,
        success: function(data){

            data = jQuery.parseJSON(data)

            $('#' + type + '_goodAgainst-' + target).html('');
            $('#' + type + '_weakAgainst-' + target).html('');

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