/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

function checkInputParameters(){

	$('.tooltip202').remove();

	//Test for input number photo
	var input_nb_photo = $('input[name="picture_per_listing"]');
	if (!$.isNumeric($(input_nb_photo, 'error', 'Hello').val()))
		addToolTip(input_nb_photo, 'error', tooltip_numeric);
	else if ($(input_nb_photo).val() > 11)
		addToolTip(input_nb_photo, 'error', tooltip_max_pictures);

}

function addToolTip(item, type, msg)
{
	if (type == 'error')
		$(item).after('<a class="tooltip202 tooltip" target="_blank"> <img src="../modules/ebay/views/img/error.png" alt="">'+msg+'</a>');
	else if (type == 'help')
		$(item).after('<a class="tooltip202 tooltip" target="_blank"> <img src="../img/admin/help.png" alt="">'+msg+'</a>');
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
function alertOnExit(active, msg){
	if (active === true){
		window.onbeforeunload = function (e) {
			var message = msg,
			e = e || window.event;
				  // For IE and Firefox
			if (e) {
				e.returnValue = message;
			}
			// For Safari
			return message;
		};	
	}
	else if (active === false)
	{
		window.onbeforeunload = null;
	}
}
