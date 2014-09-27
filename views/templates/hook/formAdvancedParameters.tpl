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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<form action="{$url|escape:'urlencode'}" method="post" class="form" id="advancedConfigForm">
    
	<fieldset style="margin-top:10px;">
		<legend><span data-dialoghelp="http://sellerupdate.ebay.co.uk/autumn2013/picture-standards" data-inlinehelp="{l s='Select the size of your main photo and any photos you want to include in your description. Go to Preferences> images. Your images must comply with eBay’s photo standards.' mod='ebay'}">{l s='Photo sizes' mod='ebay'}</span></legend>

		<label>
			{l s='Default photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizedefault" data-inlinehelp="{l s='This will be the main photo and will appear on the search result and item pages.' mod='ebay'}" class="ebay_select">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type|escape:'htmlall'}"{if $size.id_image_type == $sizedefault} selected{/if}>{$size.name|escape:'htmlall'}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="clear both"></div>

		<label>
			{l s='Main photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizebig" data-inlinehelp="{l s='This photo will appear as default photo in your listing\'s description.' mod='ebay'}" class="ebay_select">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type|escape:'htmlall'}"{if $size.id_image_type == $sizebig} selected{/if}>{$size.name|escape:'htmlall'}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div class="clear both"></div>

		<label>
			{l s='Small photo' mod='ebay'}
		</label>
		<div class="margin-form">
			<select name="sizesmall" data-inlinehelp="{l s='This photo will appear as thumbnail in your listing\'s description.' mod='ebay'}" class="ebay_select">
				{if isset($sizes) && $sizes && sizeof($sizes)}
					{foreach from=$sizes item='size'}
						<option value="{$size.id_image_type|escape:'htmlall'}"{if $size.id_image_type == $sizesmall} selected{/if}>{$size.name|escape:'htmlall'}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div style="clear:both;"></div>

		<label>
			{l s='Number of additional pictures (0 will send one picture)' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="text" name="picture_per_listing" value="{$picture_per_listing|escape:'htmlall'}">
		</div>
		<div style="clear:both;"></div>

	</fieldset>
    
    
	<fieldset style="margin-top:10px;">
        
		<legend>{l s='Others' mod='ebay'}</legend>
		{if !$is_writable && $activate_logs}<p class="warning">{l s='The log file is not writable' mod='ebay'}</p>{/if}
		<label>
			{l s='Activate Logs' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="activate_logs" value="1"{if $activate_logs} checked="checked"{/if}>
		</div>
		<div class="clear both"></div>
		{if $log_file_exists}
			<label>
				{l s='Download logs' mod='ebay'}
			</label>
			
			<div class="margin-form">
				<a href="../modules/ebay/log/request.txt" class="button">{l s='Download' mod='ebay'}</a>
			</div>
			<div class="clear both"></div>
		{/if}
        
	</fieldset>
    
    
	<fieldset style="margin-top:10px;">
        
		<legend>{l s='Sync' mod='ebay'}</legend>
		
		<label>
			{l s='Manually Sync Orders' mod='ebay'}
		</label>
		<div class="margin-form">
			
			<a href="{$url|escape:'urlencode'}&EBAY_SYNC_ORDERS=1">
				<input type="button" class="button" value="{l s='Sync Orders from eBay' mod='ebay'}" />
			</a>
	        <br>
		</div>
		<label>
			{l s='Sync Orders' mod='ebay'}
		</label>
        <div class="margin-form">
			<input type="radio" size="20" name="sync_orders_mode" class="sync_orders_mode" value="save" {if $sync_orders_by_cron == false}checked="checked"{/if}/> {l s='every 30 minutes on page load' mod='ebay'}
			<input type="radio" size="20" name="sync_orders_mode" class="sync_orders_mode" value="cron" {if $sync_orders_by_cron == true}checked="checked"{/if}/> {l s='by CRON task' mod='ebay'}<br>
	        <p><a id="sync_orders_by_cron_url" href="{$sync_orders_by_cron_url|escape:'urlencode'}" target="_blank" style="{if $sync_orders_by_cron == false};display:none{/if}">{$sync_orders_by_cron_path|escape:'urlencode'}</a></p>
        	
        </div>
		<label>
			{l s='Sync Products' mod='ebay'}
		</label>
        <div class="margin-form">
			<input type="radio" size="20" name="sync_products_mode" class="sync_products_mode" value="save" {if $sync_products_by_cron == false}checked="checked"{/if}/> {l s='on save' mod='ebay'}
			<input type="radio" size="20" name="sync_products_mode" class="sync_products_mode" value="cron" {if $sync_products_by_cron == true}checked="checked"{/if}/> {l s='by CRON task' mod='ebay'}<br>
	        <p><a id="sync_products_by_cron_url" href="{$sync_products_by_cron_url|escape:'urlencode'}" target="_blank" style="{if $sync_products_by_cron == false};display:none{/if}">{$sync_products_by_cron_path|escape:'urlencode'}</a></p>
        	
        </div>
		<div class="clear both"></div>
        
	</fieldset>   
    
    
   <fieldset style="margin-top:10px;">
       
		<legend>{l s='Ebay Data Usage' mod='ebay'}</legend>
		<label>{l s='Help us improve the eBay Module by sending anonymous usage stats' mod='ebay'} : </label>
		<div class="margin-form">
            <input type="radio" name="stats" value="0" {if isset($stats) && !$stats}checked="checked"{/if}> No thanks&nbsp;&nbsp;
            <input type="radio" name="stats" value="1" {if !isset($stats) || $stats}checked="checked"{/if}> I agree<br>
		</div>
		<div style="clear:both;"></div>
        
    </fieldset>     
    
    
</form>