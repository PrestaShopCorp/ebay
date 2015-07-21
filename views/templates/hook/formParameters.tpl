{*
* 2007-2013 PrestaShop
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

{if isset($relogin) && $relogin}
	{literal}
	<script>
		$(document).ready(function() {
			win = window.location = '{/literal}{$redirect_url|escape:'urlencode'}{literal}';
		});
	</script>
	{/literal}
{/if}
<script type="text/javascript">
	$(document).ready(function(){ldelim}
		if(regenerate_token_show)
		{ldelim}
			$('.regenerate_token_button').show();
			$('.regenerate_token_button label').css('color', 'red').html("{l s='You must regenerate your authentication token' mod='ebay'}");
			$('.regenerate_token_click').hide();
		{rdelim}
		$('.regenerate_token_click span').click(function()
		{ldelim}
			$('.regenerate_token_button').show();
			$('.regenerate_token_click').hide();
		{rdelim});
	})
</script>

	{if isset($check_token_tpl)}
	<fieldset id="regenerate_token">
		<legend>{l s='Token' mod='ebay'}</legend>
			{$check_token_tpl}	
	</fieldset>	
	{/if}
	
<form action="{$url|escape:'urlencode'}" method="post" class="form" id="configForm1">
    
	<fieldset style="margin-top:10px;">
		<legend>{l s='Account details' mod='ebay'}</legend>
		<h4>{l s='To list your products on eBay, you need to create' mod='ebay'} <a href="https://www.paypal.com/" target="_blank">{l s='a PayPal account.' mod='ebay'}</a></h4>
        
		<input type="hidden" name="ebay_shop" value="{$ebayShopValue|escape:'htmlall':'UTF-8'}" />            
        
		<label>{l s='Paypal email address' mod='ebay'} : </label>
		<div class="margin-form">

			<input type="text" size="20" name="ebay_paypal_email" value="{$ebay_paypal_email|escape:'htmlall':'UTF-8'}"/>
			<p>{l s='You have to set your PayPal e-mail account, it\'s the only payment available with this module' mod='ebay'}</p>
		</div>
        
		<label>
			{l s='Currency' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="currency" data-inlinehelp="{l s='This currency will be used for your products sold on eBay' mod='ebay'}" class="ebay_select">
				{if isset($currencies) && $currencies && sizeof($currencies)}
					{foreach from=$currencies item='currency'}
						<option value="{$currency.id_currency|escape:'htmlall':'UTF-8'}"{if $currency.id_currency == $current_currency} selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				{/if}
			</select>
		</div>        
        
		<label>{l s='Item location' mod='ebay'} : </label>
		<div class="margin-form">
			<input type="text" size="20" name="ebay_shop_postalcode" value="{$shopPostalCode|escape:'htmlall':'UTF-8'}"/>
			<p>{l s='Your shop\'s postal code' mod='ebay'}</p>
		</div>
		<label>{l s='Item Country' mod='ebay'} : </label>
		<div class="margin-form">
			<select name="ebay_shop_country" class="ebay_select">
                <option value=""></option>
			{foreach from=$ebay_shop_countries item=ebay_shop_country}
				<option value="{$ebay_shop_country.iso_code|escape:'htmlall':'UTF-8'}" {if $current_ebay_shop_country == $ebay_shop_country.iso_code} selected="selected"{/if}>{$ebay_shop_country.site_name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}							   
			</select>            
			<p>{l s='Your shop\'s country' mod='ebay'}</p>
		</div>     
		<label>
			{l s='Immediate Payment' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="immediate_payment" value="1"{if $immediate_payment} checked="checked"{/if}>
		</div>           

		<div class="show regenerate_token_click" style="display:block;text-align:center;cursor:pointer">
			<span data-inlinehelp="{l s='Use only if you get a message saying that your authentication is expired.' mod='ebay'}">{l s='Click here to generate a new authentication token.' mod='ebay'}</span>
		</div>
		<div class="hide regenerate_token_button" style="display:none;">
			<label>{l s='Regenerate Token' mod='ebay'} :</label>
			<a href="{$url|escape:'urlencode'}&action=regenerate_token">
				<input type="button" id="token-btn" class="button" value="{l s='Regenerate Token' mod='ebay'}" />
			</a>
		</div>
	</fieldset>
        
   <fieldset style="margin-top:10px;">
		<legend>{l s='Returns policy' mod='ebay'}</legend>
		<label>{l s='Please define your returns policy' mod='ebay'} : </label>
		<div class="margin-form">
			<select name="ebay_returns_accepted_option" data-dialoghelp="#returnsAccepted" data-inlinehelp="{l s='eBay business sellers must accept returns under the Distance Selling Regulations.' mod='ebay'}" class="ebay_select">
			{foreach from=$policies item=policy}
				<option value="{$policy.value|escape:'htmlall':'UTF-8'}" {if $returnsConditionAccepted == $policy.value} selected="selected"{/if}>{$policy.description|escape:'htmlall':'UTF-8'}</option>
			{/foreach}							   
			</select>
		</div>
		<div style="clear:both;"></div>
		<label>{l s='Returns within' mod='ebay'} :</label>
		<div class="margin-form">
			<select name="returnswithin" data-inlinehelp="{l s='eBay business sellers must offer a minimum of 14 days for buyers to return their items.' mod='ebay'}" class="ebay_select">
					{if isset($within_values) && $within_values && sizeof($within_values)}
						{foreach from=$within_values item='within_value'}
							<option value="{$within_value.value|escape:'htmlall':'UTF-8'}"{if isset($within) && $within == $within_value.value} selected{/if}>{$within_value.description|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					{/if}
			</select>
		</div>
		<div style="clear:both;"></div>
		<label>{l s='Who pays' mod='ebay'} :</label>
		<div class="margin-form">
			<select name="returnswhopays" class="ebay_select">
				{if isset($whopays_values) && $whopays_values && sizeof($whopays_values)}
					{foreach from=$whopays_values item='whopays_value'}
						<option value="{$whopays_value.value|escape:'htmlall':'UTF-8'}"{if isset($whopays) && $whopays == $whopays_value.value} selected{/if}>{$whopays_value.description|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<label>{l s='Any other information' mod='ebay'} : </label>
		<div class="margin-form">
			<textarea name="ebay_returns_description" cols="120" rows="10" data-inlinehelp="{l s='This description will be displayed in the returns policy section of the listing page.' mod='ebay'}">{$ebayReturns|escape:'htmlall':'UTF-8'}</textarea>
		</div>
	</fieldset>
             
	
    <fieldset style="margin-top:10px;">
 		<legend>{l s='Order Synchronization from PrestaShop to eBay' mod='ebay'}</legend>
		<label>
			{l s='Send tracking code' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="send_tracking_code" value="1"{if $send_tracking_code} checked="checked"{/if}>
		</div>
        
		<label>
			{l s='Status used to indicate product has been shipped' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="shipped_order_state" class="ebay_select">
                <option value=""></option>
				{if isset($order_states) && $order_states && sizeof($order_states)}
					{foreach from=$order_states item='order_state'}
						<option value="{$order_state.id_order_state|escape:'htmlall':'UTF-8'}"{if $order_state.id_order_state == $current_order_state} selected{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				{/if}
			</select>
		</div>        
        
 		<div style="clear:both;"></div>
     </fieldset>    
     
     
	<!-- Listing Durations -->
	<fieldset style="margin-top:10px;">
		<legend>{l s='Listing Duration' mod='ebay'}</legend>
		
		<label>
			{l s='Listing duration' mod='ebay'}
		</label>
		<div class="margin-form">

			<select name="listingdurations" data-dialoghelp="{l s='http://pages.ebay.com/help/sell/duration.html' mod='ebay'}" data-inlinehelp="{l s='The listing duration is the length of time that your listing is active on eBay.co.uk. You can have it last 1, 3, 5, 7, 10, 30 days or Good \'Til Cancelled. Good \'Til Cancelled listings renew automatically every 30 days unless all of the items sell, you end the listing, or the listing breaches an eBay policy. Good \'Til Cancelled is the default setting here to save you time relisting your items.' mod='ebay'}" class="ebay_select">
				{foreach from=$listingDurations item=listing key=key}
					<option value="{$key|escape:'htmlall':'UTF-8'}" {if $ebayListingDuration == $key}selected="selected" {/if}>{$listing|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</div>
		
        <label for="">{l s='Do you want to automatically relist' mod='ebay'}</label>
		<div class="margin-form"><input type="checkbox" name="automaticallyrelist" {if $automaticallyRelist == 'on'} checked="checked" {/if} /></div>
	</fieldset>
    
        
	<div class="margin-form" id="buttonEbayParameters" style="margin-top:5px;">
		<a href="#categoriesProgression" {if $catLoaded}id="displayFancybox"{/if}>
			<input class="primary button" name="submitSave" type="hidden" value="{l s='Save and continue' mod='ebay'}" />
			<input class="primary button" type="submit" id="save_ebay_parameters" value="{l s='Save and continue' mod='ebay'}" />
		</a>
	</div>

	<div id="ebayreturnshide" style="display:none;">{$ebayReturns|escape:'htmlall':'UTF-8'}</div>

</form>

{if $catLoaded}
	{literal}
	<script>
		var percent = 0;
		function checkCategories()
		{
			percent++;
			if (percent > 100)
				percent = 100;
			
			$("#categoriesProgression").html("{/literal}{l s='Categories loading' mod='ebay'}{literal}  <div>" + percent + " %</div>");
			if (percent < 100)
				setTimeout ("checkCategories()", 1000);
		}

		$(function(){
			$j("#displayFancybox").fancybox({
				beforeShow : function(){
					checkCategories();
					$("#save_ebay_parameters").parents('form').submit();
				},
				onStart : function(){
					checkCategories();
					$("#save_ebay_parameters").parents('form').submit();
				}
			});
		});
	</script>
	{/literal}
{/if}