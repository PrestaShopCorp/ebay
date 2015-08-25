{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}



<style> 
	{literal}
	#button_ebay_sync1{background-image:url({/literal}{$path|escape:'urlencode'}{literal}views/img/ebay.png);background-repeat:no-repeat;background-position:center 90px;width:500px;height:191px;cursor:pointer;padding-bottom:100px;font-weight:bold;font-size:25px;}
			#button_ebay_sync2{background-image:url({/literal}{$path|escape:'urlencode'}{literal}views/img/ebay.png);background-repeat:no-repeat;background-position:center 90px;width:500px;height:191px;cursor:pointer;padding-bottom:100px;font-weight:bold;font-size:15px;}
	.informations{
		padding-bottom: 3px;margin-top: 8px;
	}
	#nbproducttosync
	{
		font-weight: bold;
	}

	{/literal}
</style>
<script>
	var nbProducts = {$nb_products|escape:'htmlall':'UTF-8'};
	var nbProductsModeA = {$nb_products_mode_a|escape:'htmlall':'UTF-8'};
	var nbProductsModeB = {$nb_products_mode_b|escape:'htmlall':'UTF-8'};
	{literal}
	$(document).ready(function() {
		$(".categorySync").click(function() {
			var params = "";
			if ($(this).attr("value") > 0)
				params = "&id_category=" + $(this).attr("value");
			if ($(this).attr("checked"))
				params = params + "&action=1";
			else
				params = params + "&action=0";

			$.ajax({
				type: "POST",
				url: "{/literal}{$nb_products_sync_url|escape:'urlencode'}{literal}" + params,
				success: function(data) {
					
					nbProducts = data;
					nbProductsModeB = data;
					$('#save_and_publish').val({/literal}"{l s='Save and list' mod='ebay'} "+data+" {l s='products' mod='ebay'}"{literal});
					$('#nbproducttosync').html(data);
				}
			});
		});
	});

	$(document).ready(function() {
		$("#ebay_sync_products_mode1").click(function() {
			nbProducts = nbProductsModeA;
			$("#catSync").hide("slow");
			$('#nbproducttosync').html(nbProducts);
		});
		$("#ebay_sync_products_mode2").click(function() {
			nbProducts = nbProductsModeB;
			$("#catSync").show("slow");
			$('#nbproducttosync').html(nbProducts);
			
		});
	});

	function eBaySync(option)
	{
		$(".categorySync").attr("disabled", "true");
		$("#ebay_sync_products_mode1").attr("disabled", "true");
		$("#ebay_sync_products_mode2").attr("disabled", "true");
		$("#ebay_sync_option_resync").attr("disabled", "true");
		$("#ebay_sync_mode_1").attr("disabled", "true");
		$("#ebay_sync_mode_2").attr("disabled", "true");
		$("#button_ebay_sync1").attr("disabled", "true");
		$("#button_ebay_sync1").css("background-color", "#D5D5D5");
		$("#button_ebay_sync2").attr("disabled", "true");
		$("#button_ebay_sync2").css("background-color", "#D5D5D5");
		$("#resultSync").html("<img src=\"../modules/ebay/views/img/loading-small.gif\" border=\"0\" />");
		eBaySyncProduct(option);
	}

	function reableSyncProduct()
	{
		$(".categorySync").removeAttr("disabled", "disabled");
		$("#ebay_sync_products_mode1").removeAttr("disabled", "disabled");
		$("#ebay_sync_products_mode2").removeAttr("disabled", "disabled");
		$("#ebay_sync_option_resync").removeAttr("disabled", "disabled");
		$("#ebay_sync_mode_1").removeAttr("disabled", "disabled");
		$("#ebay_sync_mode_2").removeAttr("disabled", "disabled");
		$("#button_ebay_sync1").removeAttr("disabled", "disabled");
		$("#button_ebay_sync1").css("background-color", "#FFFAC6");
		$("#button_ebay_sync2").removeAttr("disabled", "disabled");
		$("#button_ebay_sync2").css("background-color", "#FFFAC6");
	}
	
	var counter = 0;
	function eBaySyncProduct(option)
	{
		counter++;
		$.ajax({
			type: "POST",
			url: '{/literal}{$sync_products_url|escape:'urlencode'}{literal}' + counter,
			success: function(data)
			{
				tab = data.split("|");
				$("#resultSync").html(tab[1]);
				if (tab[0] != "OK")
					eBaySyncProduct(option);
				else
					reableSyncProduct();
			}
		});
	}
	{/literal}
</script>

<div id="resultSync" style="text-align: center; font-weight: bold; font-size: 14px;"></div>


<form action="{$action_url|escape:'urlencode'}" method="post" class="form" id="configForm4">
	<fieldset style="border: 0">

        {if isset($category_alerts) && !empty($category_alerts)}
            <div class="warning big">
                {$category_alerts|escape:'htmlall':'UTF-8'}
            </div>
        {/if}

		<h4>{l s='You\'re now ready to list your products on eBay.' mod='ebay'}</h4>
		<label style="width: 250px;">{l s='List all products on eBay' mod='ebay'} : </label><br /><br />
		<div class="margin-form">
			<input type="radio" size="20" name="ebay_sync_products_mode" id="ebay_sync_products_mode1" value="A" {if $is_sync_mode_b == false}checked="checked"{/if}/> <span data-inlinehelp="{l s='All items that have specified an eBay category will be listed.' mod='ebay'}">{l s='List all products on eBay' mod='ebay'}</span>
		</div>
		<div class="margin-form">
			<input type="radio" size="20" name="ebay_sync_products_mode" id="ebay_sync_products_mode2" value="B" {if $is_sync_mode_b == true}checked="checked"{/if}/> {l s='Sync the products only in selected categories' mod='ebay'}
		</div>
		<div class="clear both"></div>
		<label style="width: 250px;">{l s='Option' mod='ebay'} : </label><br /><br />
		<div class="margin-form">
			<input type="checkbox" size="20" name="ebay_sync_option_resync" id="ebay_sync_option_resync" value="1" {if $ebay_sync_option_resync == 1}checked="checked"{/if} /> <span data-inlinehelp="{l s='All other product properties will be stay the same.' mod='ebay'}">{l s='Only synchronise price and quantity' mod='ebay'}</span>
		</div>
		<div class="clear both"></div>
		<label>{l s='Sync mod' mod='ebay'} :	</label><br /><br />
		<div class="margin-form">
			<input type="radio" size="20" name="ebay_sync_mode" id="ebay_sync_mode_2" value="2" {if $ebay_sync_mode == 2}checked="checked"{/if}/> <span data-inlinehelp="{l s='Any changes that you make to listings in PrestaShop will also be applied on eBay.' mod='ebay'}">{l s='Sync new products and update existing listings' mod='ebay'}</span>
		</div>
		<div class="margin-form">
			<input type="radio" size="20" name="ebay_sync_mode" id="ebay_sync_mode_1" value="1" {if $ebay_sync_mode == 1}checked="checked"{/if}/> <span data-inlinehelp="{l s='This will only synchronisze products that are not yet listed on eBay.' mod='ebay'}">{l s='Only sync new products' mod='ebay'}</span>
		</div>
		<div style="display: none;" id="catSync">
			<table class="table tableDnD" cellpadding="0" cellspacing="0" width="90%">
				<thead>
					<tr class="nodrag nodrop">
						<th>{l s='Select' mod='ebay'}</th>
						<th>{l s='Category' mod='ebay'}</th>
					</tr>
				</thead>
				<tbody>
					{if $categories|count == 0}
						<tr><td colspan="2">{l s='No category found.' mod='ebay'}</td></tr>
					{else}
						{foreach from=$categories item=category}
							<tr class="{$category.row_class|escape:'htmlall':'UTF-8'}"><td><input type="checkbox" class="categorySync" name="category[]" value="{$category.value|escape:'htmlall':'UTF-8'}" {$category.checked|escape:'htmlall':'UTF-8'} /><td>{$category.name|escape:'htmlall':'UTF-8'}</td></tr>
						{/foreach}
					{/if}
				</tbody>
			</table>
			{if $sync_1}
				<script>
					$(document).ready(function() {ldelim}
						eBaySync(1); 
					{rdelim});
				</script>				
			{/if}
			{if $sync_2}
				<script>
					$(document).ready(function() {ldelim}
						eBaySync(2); 
					{rdelim});
				</script>				
			{/if}
			{if $is_sync_mode_b}
				<script>
					$(document).ready(function() {ldelim}
						$("#catSync").show("slow");
						$("#ebay_sync_products_mode2").attr("checked", true);
					{rdelim});
				</script>
			{/if}
		</div><br />
		<div>
			<input type="submit" name="btnSubmitSyncAndPublish" class="primary button" value="{l s='Save and list' mod='ebay'} {$nb_products|escape:'htmlall':'UTF-8'} {l s='products' mod='ebay'}" id="save_and_publish"/>
			<input type="submit" name="btnSubmitSync" class="button" value="{l s='Save' mod='ebay'}" />
		</div>
	</fieldset>
</form>
				