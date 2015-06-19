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