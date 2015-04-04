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
		url: module_dir + "ebay/ajax/loadTablePrestaShopProducts.php?token=" + ebay_token + "&id_lang=" + id_lang + "&profile=" + id_ebay_profile,
		success : function(data) {
      
      console.log(data);
      
      $("table#PrestaShopProducts tbody #removeRow").remove();
      $("table#PrestaShopProducts tbody").html(data);
      
    }
	});
  
});

///
var loadedProducts = new Array();
function showVariations(id_product) {
  
  var elem = $('#show-vars-' + id_product);

	if (elem.attr('showing') == true) 
	{
    
		$('.variations-row[product=' + id_product +']').hide();
		elem.attr('showing', 0);
		elem.html('&#9654;');
    
	} 
	else 
	{
    
    var product_name = elem.attr('product-name');
    var multi_sku = parseInt(elem.attr('multi-sku'));
    var sync = parseInt(elem.attr('sync'));
    var blacklisted = parseInt(elem.attr('blacklisted'));
    
    console.log(multi_sku);
    console.log(sync);
    console.log(blacklisted);
    
		elem.attr('showing', 1);
		elem.html('&#9660;');

		if (loadedProducts[id_product])
			$('.variations-row[product=' + id_product +']').show();
		else
		{
			$('<img src="' + module_path + 'img/loading-small.gif" id="loading-' + id_product +'" alt="" />').insertAfter(elem);

			$.ajax({
				dataType: 'json',
				type: "POST",
				url: module_dir + 'ebay/ajax/getVariations.php?product=' + id_product + '&token=' + ebay_token + '&id_ebay_profile='+id_ebay_profile,
				success: function(variations) { 
          
					loadedProducts[id_product] = true;
					for (var i in variations)
					{
						variation = variations[i];
            
            var feedback = '';
            if (!variation.stock) {
              feedback = 'Empty stock';
            } else if (!sync) {
              feedback = 'Synchronisation disabled';
            } else if (blacklisted) {
              feedback = 'Product not selected';
            }

						$('#product-' + id_product).after('<tr class="variations-row ' + (i%2 == 0 ? 'alt_row':'') + '" product="' + id_product + '"> \
							<td>' + product_name + ' ' + variation.name + '</td> \
              <td class="center">' + variation.stock + '</td> \
							<td colspan="4"></td> \
							<td>'+ (variation.id_product_ref ? 'Link' : 'No ad') + '</td> \
              <td>' + feedback + '</td> \
						</tr>');
					}
					$('#loading-' + id_product).remove();
          
				}
			});
      
		}
	}
}

/*

function loadCategoryMatch(id_category) {
	$.ajax({
		async: false,
		type: "POST",
		url: module_dir + 'ebay/ajax/loadCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&ch_cat_str=' + categories_ebay_l['no category selected'] + "&profile=" + id_ebay_profile,
		success: function(data) { $("#categoryPath" + id_category).html(); }
	});
}

function changeCategoryMatch(level, id_category) {
	var levelParams = "&level1=" + $("#categoryLevel1-" + id_category).val();

	if (level > 1) levelParams += "&level2=" + $("#categoryLevel2-" + id_category).val();
	if (level > 2) levelParams += "&level3=" + $("#categoryLevel3-" + id_category).val();
	if (level > 3) levelParams += "&level4=" + $("#categoryLevel4-" + id_category).val();
	if (level > 4) levelParams += "&level5=" + $("#categoryLevel5-" + id_category).val();

	$.ajax({
		type: "POST",
		url: module_dir + 'ebay/ajax/changeCategoryMatch.php?token=' + ebay_token + '&id_category=' + id_category + '&time=' + module_time + '&level=' + level + levelParams + '&ch_cat_str=' + categories_ebay_l['no category selected'] + '&profile=' + id_ebay_profile,
		success: function(data) { $("#categoryPath" + id_category).html(data); }
	});
}

var loadedCategories = new Array();

function toggleSyncProduct(obj)
{
	var product_id = $(obj).attr('product');
}
*/