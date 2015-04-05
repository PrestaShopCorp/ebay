/*
* 2007-2014 PrestaShop
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
*	@author    PrestaShop SA <contact@prestashop.com>
*	@copyright	2007-2014 PrestaShop SA
*	@license   http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
  
  /* PAGINATION
	$("#pagination").children('li').click(function() {
    
		var p = $(this).html();
		var li = $("#pagination").children('li.current');
    
		if ($(this).attr('class') == 'prev')
		{
			var liprev = li.prev();
			if (!liprev.hasClass('prev'))
			{
				liprev.trigger('click');
			}
			return false;
		}
    
		if ($(this).attr('class') == 'next')
		{
			var linext = li.next();
			if (!linext.hasClass('next'))
			{
				linext.trigger('click');
			}
			return false;
		}
    
		$("#pagination").children('li').removeClass('current');
		$(this).addClass('current');
		$("#textPagination").children('span').html(p);
		$.ajax({
			type: "POST",
			dataType: "json",
			url: module_dir + "ebay/ajax/saveCategories.php?token=" + ebay_token + "&profile=" + id_ebay_profile,
			data: $('#configForm2').serialize()+"&ajax=true",
			success : function(data)
			{
				if (data.valid)
				{
					$.ajax({
						type: "POST",
						url: module_dir + "ebay/ajax/loadTableCategories.php?token=" + ebay_token + "&p=" + p + "&profile=" + id_ebay_profile + "&id_lang=" + id_lang + "&ch_cat_str=" + categories_ebay_l["no category selected"] + "&ch_no_cat_str=" + categories_ebay_l["no category found"] + "&not_logged_str=" + categories_ebay_l["You are not logged in"] + "&unselect_product=" + categories_ebay_l["Unselect products"]  ,
						success : function(data) {
							$("form#configForm2 table tbody #removeRow").remove(); $("form#configForm2 table tbody").html(data);
						}
					});
				}
			}
		});
	})  
  */
  
	$.ajax({
		type: "POST",
		url: module_dir + "ebay/ajax/loadTableOrphanAds.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile,
		success : function(data) {
      
      console.log(data);
      
      $("table#OrphanAds tbody #removeRow").remove();
      $("table#OrphanAds tbody").html(data);
      
    }
	});
  
});

