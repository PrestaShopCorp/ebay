/*
* 2007-2015 PrestaShop
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
*	@copyright	2007-2015 PrestaShop SA
*	@license   http://opensource.org/licenses/afl-3.0.php	Academic Free License (AFL 3.0)
*	International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {
  
	$("#stores_pagination").children('li').click(function() {
		var p = $(this).html();
		var li = $("#stores_pagination").children('li.current');
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
		$("#stores_pagination").children('li').removeClass('current');
		$(this).addClass('current');
		$("#textStoresPagination").children('span').html(p);
    
		$.ajax({
			type: "POST",
			dataType: "json",
			url: module_dir + "ebay/ajax/saveStoreCategories.php?token=" + ebay_token + "&profile=" + id_ebay_profile,
			data: $('#configFormStoreCategories').serialize()+"&ajax=true",
			success : function(data)
			{
				if (data.valid)
        {
					$.ajax({
						type: "POST",
						url: module_dir + "ebay/ajax/loadTableStoreCategories.php?token=" + ebay_token + "&p=" + p + "&profile=" + id_ebay_profile + "&id_lang=" + id_lang + "&ch_no_cat_str=" + store_categories_ebay_l["no category found"] + "&not_logged_str=" + store_categories_ebay_l["You are not logged in"] + "&unselect_product=" + store_categories_ebay_l["Unselect products"]  ,
						success : function(data) {
							$("form#configFormStoreCategories table tbody #removeRow").remove(); $("form#configFormStoreCategories table tbody").html(data);
						}
					});
				}
			}
		});
    
	})  
  
	$.ajax({
		type: "POST",
		url: module_dir + "ebay/ajax/loadTableStoreCategories.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile + '&ch_no_cat_str=' + store_categories_ebay_l['no category found'] + '&not_logged_str=' + store_categories_ebay_l['You are not logged in'] + '&unselect_product=' + store_categories_ebay_l['Unselect products'],
		success : function(data) { $("form#configFormStoreCategories table tbody #removeRow").remove(); $("form#configFormStoreCategories table tbody").html(data); }
	});
  
});
