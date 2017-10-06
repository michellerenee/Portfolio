
var sortable_cache = $('#sortable').html();
$('#sortable').sortable({
    items : '.sortable-item',
    handle: '.sortable-handle',
    distance: 5,
    update: function() {
        var data_array	= []; // opretter et tom array
        $('.sortable-item').each(function(index) { //for hvert ting i id'et sortable med class sortable-item kører vi en funktion med index (nummer fra 0-XX)
            data_array[index] = {id: $(this).attr('id')}; // Tilføj id'et fra hvert element til et array

            // Opdatér sorteringsnummeret i første kolonne
            $('#' + $(this).attr('id') + ' td:first-child').text(index + 1);
        });

        var data_object =
        {
            type	: $(this).data('type'),
            section	: $(this).data('section'),
            data	: data_array
        };

        // Do ajax request to toggle_status.php, send the jsonObject as data and use post. Return the data from
        // the php-file as json-encoded
        $.ajax({
            type        : 'post',
            url         : 'includes/sortable.php',
            data        : data_object,
            dataType    : 'json',
            // On success, check if the returned status is false. If it is, return order to the previous
            success     : function(data){
                if(!data.status){
                    $('#sortable').html(sortable_cache);
                }
            }
        });
    }
});