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
        <label>
			{l s='Do not send variations images' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="picture_skip_variations" value="1" {if $picture_skip_variations} checked="checked"{/if}>
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
			{if $log_file_exists}
			<a href="#" class ='logs'  class="button" data-inlinehelp="{l s='When log file size reaches 100 mo, log file is emptied automatically.' mod='ebay'}">{l s='Download' mod='ebay'}</a>
			{/if}
		</div>
		<div class="clear both"></div>

        
		<label>{l s='Logs Conservation Duration' mod='ebay'} : </label>
		<div class="margin-form">
            <input type="text" name="logs_conservation_duration" value="{$logs_conservation_duration|escape:'htmlall':'UTF-8'}">
		</div>        
        
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
		{if $help_Cat_upd.ps_version > '1.4.11'}
		<label>
			{l s='Synch cancellations, refunds and returns' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="radio" size="20" name="sync_orders_returns_mode" class="sync_orders_returns_mode" value="save" {if $sync_orders_returns_by_cron == false}checked="checked"{/if}/> {l s='every 30 minutes on page load' mod='ebay'}
			<input type="radio" size="20" name="sync_orders_returns_mode" class="sync_orders_returns_mode" value="cron" {if $sync_orders_returns_by_cron == true}checked="checked"{/if}/> {l s='by CRON task' mod='ebay'}<br>
			<p><a id="sync_orders_returns_by_cron_url" href="{$sync_orders_returns_by_cron_url|escape:'urlencode'}" target="_blank" style="{if $sync_orders_returns_by_cron == false};display:none{/if}">{$sync_orders_returns_by_cron_path|escape:'urlencode'}</a></p>

		</div>
		{/if}
		<label>
			{l s='Always override Business Policies' mod='ebay'}
		</label>
		<div class="margin-form">
			<input type="checkbox" name="activate_resynchBP" value="1"{if $activate_resynchBP == 1} checked="checked"{/if} data-inlinehelp="{l s='If activiated, Business Policies created by PrestaShop will be overriden at every product synchronisation.' mod='ebay'}">

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

		<legend >{l s='Category definition & upgrade' mod='ebay'}</legend>
		<label >{l s='Category definition comparison tool' mod='ebay'} : </label>
		<div class="margin-form">
			<a name="comparison"  id="check_categories" href="#" target="_blank" class="button" data-inlinehelp="{l s='Compare your category definitions with last available category definitions from eBay' mod='ebay'}">{l s='Start comparison' mod='ebay'}</a>
		</div>
		<div style="clear:both;"></div>

		<div id="check_categories_logs" style="display:none;">
			</br>
			<span> {l s='eBay categories used in your eBay module configuration compared to last available category definitions from eBay' mod='ebay'}</span></br>
			</br><table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
				<thead>
				<tr class="nodrag nodrop">
					<th style="width:70%">
						{l s='Ebay Category Configured in PrestaShop' mod='ebay'}
					</th>

					<th style="width:30%">
						{l s='Statut' mod='ebay'}
					</th>
				</tr>
				</thead>
				<tbody id="table_resynch">
				</tbody>
			</table>
		</div>

		<div id="new_cat" style="display: none;    margin-top: 12px;max-height: 300px; overflow-y: scroll;">
			<span> {l s='Some categories are existing in the new definition of categories from eBay but not in your definition. You might want to reload your categories' mod='ebay'}</span></br>
			<ul id="categories_new">

			</ul>

		</div>

		<div id="div_resynch"  style="display:none; height: 100px;   text-align: center; margin-top: 27px;font-family: sans-serif;
    font-size: 14px;">
			<span> {l s='Have you read ' mod='ebay'}<a class="kb-help" style ="width: auto;height: 20px;background-image: none;" data-errorcode="{$help_Cat_upd.error_code}" data-module="ebay" data-lang="{$help_Cat_upd.lang}" module_version="{$help_Cat_upd.module_version}" prestashop_version="{$help_Cat_upd.ps_version}" href="" target="_blank">{l s='this article' mod='ebay'}</a>{l s=' about category definition & reloading?' mod='ebay'}</span>
			</br>
			</br>
			<input type="checkbox" name="accepted" id="accepted" value="yes" ><span style="color: red;"> {l s='I have understood all my categories will need to reconfigured manually' mod='ebay'}</span> <br>
			</br><a class='primary button disable_link link_resynch'id="ReCategoriespar" href ="{$smarty.server.REQUEST_URI}&resynchCategories='1'">{l s='Reaload category definition' mod='ebay'}</a>
		</div>
		<div style="margin-top: 30px">
		<label>{l s='Category definition upgrade tool' mod='ebay'} : </label>
		<div class="margin-form">
			<a name="resynch" id="ResynchCategories" class="button" href ="#div_resynch" data-inlinehelp="{l s='Upgrade category definition with last definition from eBay. You will need to reconfigure all your categories.' mod='ebay'}">{l s='Start upgrade' mod='ebay'}...</a>
		</div>
		</div>
		<div style="clear:both;"></div>
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


			$('.logs').click(function(e) {
				e.preventDefault();
				window.open(module_dir + 'ebay/ajax/checkLogs.php?token={/literal}{$ebay_token|escape:'urlencode'}{literal}&action=getLogs');;
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

			$('.sync_orders_returns_mode').change(function() {
				if ($(this).val() == 'cron') {
					$('#sync_orders_returns_by_cron_url').show();
				} else {
					$('#sync_orders_returns_by_cron_url').hide();
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

			$(function() {
				$('#check_categories').click(function(e){
					e.preventDefault();
					$('#check_categories_logs').show();
					$('#table_resynch tr').remove();
					$('#table_resynch').append("<tr style='font-weight: bold;'><td colspan='2' ><img src='{/literal}{$_module_dir_|escape:'htmlall':'UTF-8'}{literal}ebay/views/img/loading-small.gif' alt=''/></td></tr>");

					comparation(1,false,false,0);
				});
			});

			function comparation(step,id_categories, nextDatas,encour,size){

				$.ajax({
					dataType: 'json',
					type: 'POST',
					url: module_dir + 'ebay/ajax/checkCategory.php',
					data: "token={/literal}{$ebay_token|escape:'urlencode'}{literal}&action=checkCategories&id_profile_ebay={/literal}{$id_profile_ebay|escape:'urlencode'}{literal}&step=" + step + "&id_categories=" + id_categories,
					beforeSend: function() {

					},
					success: function( data ) {

						if (step == 1 ||step == 2) {

							if(nextDatas == false){
								nextDatas = data;
							}

							if(step == 1) {
								size = nextDatas.length;
								size= size - 1;
							}

							categoryId = nextDatas[0].CategoryID;
							nextDatas.shift();

							if (nextDatas.length == 0) {
									comparation(3,false,false,false,false);
							} else{
								if(step == 2) {
									$('#table_resynch tr').last().remove();
									encour = encour +1;

									$('#table_resynch').append("<tr class='version_ok'><td>" + encour + "/"+ size +"</td><td style='color: #72C279;'></td></tr>");
								}
								comparation(2, categoryId, nextDatas,encour,size);
							}

						}
						if (step == 3) {
							$('#table_resynch tr').remove();
							if (data['table'] != null) {
								$.each(data['table'], function (key, value) {
									if (value != 1) {
										$('#table_resynch').append("<tr class='fail'><td>" + key + "</td><td style='color: red;'>" + category_false + "</td></tr>");
									} else {
										$('#table_resynch').append("<tr class='version_ok'><td>" + key + "</td><td style='color: #72C279;'>" + category_true + "</td></tr>");
									}
								});

								$('#check_category_progress').show();
								$('#check_category_logs').show();

								if ($('.fail').length) {
									$('#table_resynch').append("<tr style='background-color: red;font-weight: bold;'><td colspan='2' >" + categories_false + "</td></tr>");
								} else {
									$('#table_resynch').append("<tr style='background-color: #DFF2BF;font-weight: bold;'><td colspan='2' >" + categories_true + "</td></tr>");

								}
							} else {
								$('#table_resynch').append("<tr style='background-color: red;font-weight: bold;'><td colspan='2' >" + categories_null + "</td></tr>");
							}
							if (data['new'] != false) {
								$.each(data['new'], function (key, value) {

									$('ul#categories_new').append("<li>" + value['name'] + "</li>");
								})
								$('#new_cat').show();
							}
						}
					}
				});

			}
			$('#ResynchCategories').fancybox();
			$('#accepted').change(function(){
				if($('#ReCategoriespar').hasClass('disable_link')){
					$('#ReCategoriespar').removeClass('disable_link');
				} else {
					$('#ReCategoriespar').addClass('disable_link');
				}
			});

			{/literal}
		</script>

<script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/advancedParameters.js"></script>
</form>

<script type="text/javascript">
	var category_true = "{l s='Category definition is already last version' mod='ebay'}";
	var category_false = "{l s='Category definition is not in last version, you may need to reload categories' mod='ebay'}";
	var categories_true = "{l s='All configured categories are already using last category definition version, no action to be taken.' mod='ebay'}";
	var categories_false = "{l s='The following categories are not configured based on the last category definition, you may need to reload categories.' mod='ebay'}";
	var categories_null = "{l s='No category to compare because you did not set up any category in tab Settings > Categories' mod='ebay'}";

</script>

<style>
	.disable_link {
		pointer-events: none;
		cursor: default;
		opacity: 0.4;
	}
	.link_resynch {
		border: 1px solid;
		border-radius: 4px;
		width: 20%;
		text-decoration: none;
		margin: auto;
		color: white;
		padding: 5px;
		background-color: rgb(23, 119, 182);
	}
</style>