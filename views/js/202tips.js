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
$(function() {
	$j("[data-dialoghelp], [data-inlinehelp]").each(function(){
		var attr = $j(this).attr('data-dialoghelp');
		var tooltip = $j(this).attr('data-inlinehelp');
		
		if (attr != undefined || tooltip != undefined){
			// Fancybox
			var fancybox = (attr != undefined && attr.length > 0 && attr[0] == "#");

			if (attr != undefined)
				attr = 'href="' + attr +'"';
			else
				attr = '';

			var content = "";
			// Img
			content += '<a ' + attr + ' class="' + (fancybox === true ? 'fancybox' : '') + ' ' + (tooltip ? 'tooltip' : '')  + '" title="' + (tooltip ? tooltip : '') + '" target="_blank">';
			content += ' <img src="../img/admin/help.png" alt="" />';
			content += '</a>';
			// Insert
			$j(this).after(content);
			// Init
			$j(".fancybox").fancybox({
    		});
			$j('.tooltip').tooltipster({
	    		position : 'right'
	    	});
		}
	});
});