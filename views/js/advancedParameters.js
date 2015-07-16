function checkInputParameters(){

	$('.tooltip202').remove();

	//Test for input number photo
	var input_nb_photo = $('input[name="picture_per_listing"]');
	if (!$.isNumeric($(input_nb_photo, 'error', 'Hello').val()))
		addToolTip(input_nb_photo, 'error', 'You must enter a numeric field');
	else if ($(input_nb_photo).val() > 11)
		addToolTip(input_nb_photo, 'error', 'The maximum number is 12, so you can put 11 to maximum');

}

function addToolTip(item, type, msg)
{
	if (type == 'error')
		$(item).after('<a class="tooltip202 tooltip" target="_blank"> <img src="../modules/ebay/views/img/error.png" alt="">'+msg+'</a>')
	else if (type == 'help')
		$(item).after('<a class="tooltip202 tooltip" target="_blank"> <img src="../img/admin/help.png" alt="">'+msg+'</a>')
}

function launchDatabaseChecking(i){
	$('#check_database_progress > .progress > .progress-bar').attr('aria-valuenow', i-1);
	refreshDatabaseProgress();
	$.ajax({
		type: 'POST',
		url: module_dir + 'ebay/ajax/checkDatabase.php',
		data: "token="+token+"&action=checkSpecific&value="+i,
		dataType: 'json',
		success: function( data ){
			$.each(data, function( key, value ) {
				$.each(value, function( index, val ) {
					if (val.status != 'stop')
						$('#check_database_logs > table > tbody').append('<tr class="'+val.status+'"><td></td><td>'+val.action+'</td><td>'+val.result+'</td></tr>');

					if (val.status != 'stop')
						launchDatabaseChecking(i+1);


					if (val.status == 'stop'){
						$('#check_database_logs > table > tbody > tr.success').hide();
						if ($('#check_database_logs > table > tbody > tr.success').length >= $('#check_database_progress').attr('data-nb_database'))
							$('#check_database_logs > table > tbody').append('<tr class="success"><td></td><td colspan="2" >All is good</td></tr>');
					}
				});
			});
			



		}
	});
}
function refreshDatabaseProgress(){
	$('#check_database_progress > .progress > .progress-bar').text($('#check_database_progress > .progress > .progress-bar').attr('aria-valuenow')+" / "+$('#check_database_progress').attr('data-nb_database'));
}