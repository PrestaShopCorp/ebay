{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="ebayListings">
	<p class="center">
		<button class="button">{l s='See eBay listings' mod='ebay'}</button>
	</p>
</div>
<script type="text/javascript">
	// <![CDATA[
	var content_ebay_listings = $("#ebayListings button");
	content_ebay_listings.bind('click', 'button', function(){
		$.ajax({
			type: "POST",
			url: module_dir+'ebay/ajax/getEbayListings.php',
			data: "token="+ebay_token+"&id_employee={$id_employee|escape:'htmlall':'UTF-8'}&id_shop="+id_shop,
			success: function(data)
			{
				$('#ebayListings').fadeOut(400, function(){
					$(this).html(data).fadeIn();
				})
			}
		});
	});
	//]]>
</script>
