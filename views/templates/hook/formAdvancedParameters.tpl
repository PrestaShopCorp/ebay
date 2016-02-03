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
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
						<option value="{$size.id_image_type|escape:'htmlall':'UTF-8'}"{if $size.id_image_type == $sizedefault} selected{/if}>{$size.name|escape:'htmlall':'UTF-8'}</option>
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
						<option value="{$size.id_image_type|escape:'htmlall':'UTF-8'}"{if $size.id_image_type == $sizebig} selected{/if}>{$size.name|escape:'htmlall':'UTF-8'}</option>
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
						<option value="{$size.id_image_type|escape:'htmlall':'UTF-8'}"{if $size.id_image_type == $sizesmall} selected{/if}>{$size.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				{/if}
			</select>
		</div>
		<div style="clear:both;"></div>

		<label>
			{l s='Number of additional pictures (0 will send one picture)' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="text" name="picture_per_listing" value="{$picture_per_listing|escape:'htmlall':'UTF-8'}" onchange="checkInputParameters()">
		</div>
		<div style="clear:both;"></div>
		<label>
			{l s='Send new product images on the next synchronization' mod='ebay'}
		</label>
		<div class="margin-form">
			<a id="reset-image" href="#" target="_blank" class="button">Active</a>
			<p id="reset-image-result"></p>
		</div>
		<div style="clear:both;"></div>
	</fieldset>
    
    
	<fieldset style="margin-top:10px;">
        
		<legend>{l s='Logs' mod='ebay'}</legend>
        
		<label>
			{l s='API Logs' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="api_logs" value="1"{if $api_logs} checked="checked"{/if}>
		</div>        
        
		{if !$is_writable && $activate_logs}<p class="warning">{l s='The log file is not writable' mod='ebay'}</p>{/if}
		<label>
			{l s='Applicative Logs' mod='ebay'}
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
				<a href="../modules/ebay/log/request.txt" target="_blank" class="button">{l s='Download' mod='ebay'}</a>
			</div>
			<div class="clear both"></div>
		{/if}
        
		<label>{l s='Logs Conservation Duration' mod='ebay'} : </label>
		<div class="margin-form">
            <input type="text" name="logs_conservation_duration" value="{$logs_conservation_duration|escape:'htmlall':'UTF-8'}">
		</div>        
        
	</fieldset>
    
    <fieldset style="margin-top:10px;">
       
        <legend>{l s='EAN Sync' mod='ebay'}</legend>

		<label>{l s='Synchronize EAN with :' mod='ebay'}</label>
		<div class="margin-form">
			<select name="synchronize_ean" class="ebay_select">
				<option value="">{l s='Do not synchronise' mod='ebay'}</option>
				<option value="EAN"{if "EAN" == $synchronize_ean} selected{/if}>{l s='EAN' mod='ebay'}</option>
				{*<option value="SUP_REF"{if "SUP_REF" == $synchronize_ean} selected{/if}>{l s='Supplier Reference' mod='ebay'}</option>*}
				<option value="REF"{if "REF" == $synchronize_ean} selected{/if}>{l s='Reference' mod='ebay'}</option>
				<option value="UPC"{if "UPC" == $synchronize_ean} selected{/if}>{l s='UPC' mod='ebay'}</option>
			</select>
		</div>
		<label>{l s='Synchronize MPN with :' mod='ebay'}</label>
		<div class="margin-form">
			<select name="synchronize_mpn" class="ebay_select">
				<option value="">{l s='Do not synchronise' mod='ebay'}</option>
				<option value="EAN"{if "EAN" == $synchronize_mpn} selected{/if}>{l s='EAN' mod='ebay'}</option>
				{*<option value="SUP_REF"{if "SUP_REF" == $synchronize_mpn} selected{/if}>{l s='Supplier Reference' mod='ebay'}</option>*}
				<option value="REF"{if "REF" == $synchronize_mpn} selected{/if}>{l s='Reference' mod='ebay'}</option>
				<option value="UPC"{if "UPC" == $synchronize_mpn} selected{/if}>{l s='UPC' mod='ebay'}</option>
			</select>
		</div>
		<label>{l s='Synchronize UPC with :' mod='ebay'}</label>
		<div class="margin-form">
			<select name="synchronize_upc" class="ebay_select">
				<option value="">{l s='Do not synchronise' mod='ebay'}</option>
				<option value="EAN"{if "EAN" == $synchronize_upc} selected{/if}>{l s='EAN' mod='ebay'}</option>
				{*<option value="SUP_REF"{if "SUP_REF" == $synchronize_upc} selected{/if}>{l s='Supplier Reference' mod='ebay'}</option>*}
				<option value="REF"{if "REF" == $synchronize_upc} selected{/if}>{l s='Reference' mod='ebay'}</option>
				<option value="UPC"{if "UPC" == $synchronize_upc} selected{/if}>{l s='UPC' mod='ebay'}</option>
			</select>
		</div>
		<label>{l s='Synchronize ISBN with :' mod='ebay'}</label>
		<div class="margin-form">
			<select name="synchronize_isbn" class="ebay_select">
				<option value="">{l s='Do not synchronise' mod='ebay'}</option>
				<option value="EAN"{if "EAN" == $synchronize_isbn} selected{/if}>{l s='EAN' mod='ebay'}</option>
				{*<option value="SUP_REF"{if "SUP_REF" == $synchronize_isbn} selected{/if}>{l s='Supplier Reference' mod='ebay'}</option>*}
				<option value="REF"{if "REF" == $synchronize_isbn} selected{/if}>{l s='Reference' mod='ebay'}</option>
				<option value="UPC"{if "UPC" == $synchronize_isbn} selected{/if}>{l s='UPC' mod='ebay'}</option>
			</select>
		</div>
		<label>
			{l s='Option \'Does Not Apply\'' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="ean_not_applicable" value="1"{if $ean_not_applicable} checked="checked"{/if} data-inlinehelp="{l s='If you check this box, the module will send EAN value &quot;Does not apply&quot; when none of EAN, ISBN or UPC is set.' mod='ebay'}">
		</div>

        <div style="clear:both;"></div>
        
    </fieldset>

	<fieldset style="margin-top:10px;">
        
		<legend>{l s='Sync' mod='ebay'}</legend>
		
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
       
		<legend>{l s='Orders Collection Duration' mod='ebay'}</legend>
		<label>{l s='Since when fetch orders (in days, change if you receive more than 100 orders per fortnight)' mod='ebay'} : </label>
		<div class="margin-form">
            <input type="text" name="orders_days_backward" value="{$orders_days_backward|escape:'htmlall':'UTF-8'}">
		</div>
		<div style="clear:both;"></div>
        
    </fieldset>
      
    <fieldset style="margin-top:10px;">
       
		<legend>{l s='Check Database' mod='ebay'}</legend>
		<label>{l s='Click on "Start checking" if you want to proceed to verify your eBay database' mod='ebay'} : </label>
		<div class="margin-form">
        	<a id="check_database" href="#" target="_blank" class="button">Start checking</a>
		</div>
		<div style="clear:both;"></div>
		<div id="check_database_progress" style="display:none;">
			<div class="progress">
			  <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="">
			  </div>
			</div>	
		</div>
		<div id="check_database_logs" style="display:none;">
			<table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
				<thead>
					<tr class="nodrag nodrop">
						<th style="width:10%">
							{l s='Status' mod='ebay'}
						</th>
						<th style="width:45%">
							{l s='Description' mod='ebay'}
						</th>
						<th style="width:45%">
							{l s='Result' mod='ebay'}
						</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
        
    </fieldset>
    
   <fieldset style="margin-top:10px;">
       
		<legend>{l s='eBay module Data Usage' mod='ebay'}</legend>
		<label>{l s='Help us improve the eBay Module by sending anonymous usage stats' mod='ebay'} : </label>
		<div class="margin-form">
            <input type="radio" name="stats" value="0" {if isset($stats) && !$stats}checked="checked"{/if}> {l s='No thanks' mod='ebay'} &nbsp;&nbsp;
            <input type="radio" name="stats" value="1" {if !isset($stats) || $stats}checked="checked"{/if}> {l s='I agree' mod='ebay'}<br>
		</div>
		<div style="clear:both;"></div>
        
    </fieldset>
    
	<div class="margin-form" id="buttonEbayParameters" style="margin-top:5px;">
		<a href="#categoriesProgression">
			<input class="primary button" name="submitSave" type="hidden" value="{l s='Save and continue' mod='ebay'}" />
			<input class="primary button" type="submit" id="save_ebay_advanced_parameters" value="{l s='Save and continue' mod='ebay'}" />
		</a>
	</div>        
    
	{literal}
		<script>
			var token = "{/literal}{$ebay_token|escape:'urlencode'}{literal}";
			$(document).ready(function() {
				setTimeout(function(){					
					$('#ebay_returns_description').val($('#ebayreturnshide').html());
				}, 1000);
			});
			
			$('#token-btn').click(function() {
					window.open(module_dir + 'ebay/pages/getSession.php?token={/literal}{$ebay_token|escape:'urlencode'}{literal}');			
			});
            
            $('.sync_products_mode').change(function() {
                if ($(this).val() == 'cron') {
                    $('#sync_products_by_cron_url').show();
                } else {
                    $('#sync_products_by_cron_url').hide();
                }
            });

            $('.sync_orders_mode').change(function() {
                if ($(this).val() == 'cron') {
                    $('#sync_orders_by_cron_url').show();
                } else {
                    $('#sync_orders_by_cron_url').hide();
                }
            });

            $(function() {
				$('#reset-image').click(function(e){
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: module_dir + 'ebay/ajax/deleteProductImage.php',
						data: "token={/literal}{$ebay_token|escape:'urlencode'}{literal}&action=delete-all",
						beforeSend: function() {
						    $('#reset-image-result').css('color', 'orange').text("{/literal}{l s='Activation in progress...' mod='ebay'}{literal}");
						}
					}).done(function( data ) {
						if (data == 'success')
							$('#reset-image-result').css('color', 'green').text("{/literal}{l s='New images will be included in next synchronization.' mod='ebay'}{literal}");
						else
							$('#reset-image-result').css('color', 'red').text("{/literal}{l s='An error has occurred.' mod='ebay'}{literal}");
					}).fail(function() {
						$('#reset-image-result').css('color', 'red').text("{/literal}{l s='An error has occurred.' mod='ebay'}{literal}");
					})
				});
			});

			$(function() {
				$('#check_database').click(function(e){
					e.preventDefault();
					// Premier tour : Récuperer le nombre de table
					// Foreach de toutes les tables
					$.ajax({
						type: 'POST',
						url: module_dir + 'ebay/ajax/checkDatabase.php',
						data: "token={/literal}{$ebay_token|escape:'urlencode'}{literal}&action=getNbTable",
						beforeSend: function() {
							$('#check_database_logs tbody tr').remove();
						    // $('#reset-image-result').css('color', 'orange').text("{/literal}{l s='Activation in progress...' mod='ebay'}{literal}");
						},
						success: function( data ){
							$('#check_database_progress').attr('data-nb_database', data);
							$('#check_database_progress').show();
							$('#check_database_logs').show();
							launchDatabaseChecking(1);
						}
					});
				});
			});
		</script>
	{/literal}    
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/advancedParameters.js"></script>
</form>
