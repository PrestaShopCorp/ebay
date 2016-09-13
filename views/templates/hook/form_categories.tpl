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

<div>
	{if isset($alerts) && !empty($alerts)}
	<div class="warning big">
		{$alerts|escape:'htmlall':'UTF-8'}
	</div>
	{/if}
	{if (isset($load_cat) && $load_cat == true) && (isset($launch_load_cat) && $launch_load_cat == true)}
	<table id="load_cat_ebay" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
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
			<tr id="cat_parent" class="load">
				<td></td>
				<td>{l s='Loading list of eBay categories' mod='ebay'}</td>
				<td>{l s='In progress' mod='ebay'}</td>
			</tr>
		</tbody>
	</table>
    {elseif isset($load_cat) && $load_cat == true}
        {l s='Loading of categories did not finish, please retry with button below.' mod='ebay'}<br/>
        <a href="{$url_categories|escape:'htmlall':'UTF-8'}"><input type="button" class="button" value="{l s='Load categories' mod='ebay'}"/></a>
    {/if}
	<p {if isset($load_cat) && $load_cat == true}class="hidden importCatEbay"{/if}>
		<b>{l s='Select a category' mod='ebay'}</b>
		<br />
		{l s='To list your products on eBay, you need to map your Prestashop category with an eBay category.' mod='ebay'} <br />
		{l s='The button below will automatically map your categories with eBay categories. We recommend you check that you’re happy with the category chosen and amend if necessary.' mod='ebay'}
	</p>
	{if $form_categories == 0}
		<form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=2&section=category&action=suggestCategories" method="post" class="form{if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}" id="configForm2SuggestedCategories">
			<input class="button" name="submitSave" type="submit" value="{l s='Suggest eBay categories' mod='ebay'}" data-inlinehelp="{l s='Automatically map your Prestashop categories with the correct eBay category. ' mod='ebay'}" />
		</form>
		
		<div class="warning big tips {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}">
           	{l s='For your first synchronisation, we advise you to synchronize only a one category in order to test the link between your shop and eBay. And then add more categories progressively.' mod='ebay'}
        </div>

	{/if}
	<!---------------------------->
	<p {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}>
		<b>{l s='Your eBay selling price' mod='ebay'}</b>
		<br />
		{l s='You can adjust the price that you sell your items for on eBay in relation to your PrestaShop price by a fixed amount or percentage.' mod='ebay'}
		{l s='You might want to increase your selling price to take into account the' mod='ebay'} <a href="{l s='http://sellercentre.ebay.co.uk/final-value-fees-business-sellers' mod='ebay'}">{l s='fees for selling on eBay.' mod='ebay'}</a> {l s='Or, reduce your price to be competitive.' mod='ebay'}
		{l s='Take a look at what similar items are selling for on' mod='ebay'} <a href="{l s='eBay.co.uk' mod='ebay'}">{l s='eBay site' mod='ebay'}</a>.
	</p>
	<!---------------------------->
	<p {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}>
		<b>{l s='List on eBay' mod='ebay'}</b>
		<br />
		{l s='Choose which of your items you want to list on eBay by ticking the box.' mod='ebay'}
	</p>
    
    <p {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}>
    	<b>{l s='Filter PrestaShop categories' mod='ebay'}</b> : <input id="cat-filter" type="search" placeholder="{l s='Category name' mod='ebay'}" />
    </p>
</div>
<br />

<div id="cat-pagination-holder" {if isset($load_cat) && $load_cat == true}class="hidden importCatEbay"{/if}></div>

<form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=2&section=category" method="post" class="form {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}" id="configForm2">	<table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th style="width:110px;">
					{l s='PrestaShop category' mod='ebay'}
				</th>
				<th>
					<span data-inlinehelp="{l s='Only products with a mapped category will be listed.' mod='ebay'}">{l s='eBay category' mod='ebay'} <span style="color:red">{l s='(required)' mod='ebay'}</span></span>
				</th>
				<th style="width:185px;">
					<span data-inlinehelp="{l s='Increase or decrease the sales price of the items listed on eBay.' mod='ebay'}">{l s='Nb products in category' mod='ebay'}</span>
				</th>                
				<th style="width:185px;">
					<span data-inlinehelp="{l s='Increase or decrease the sales price of the items listed on eBay.' mod='ebay'}">{l s='eBay selling price' mod='ebay'}</span>
				</th>				
				<th class="center">
					<span data-inlinehelp="{l s='All products with mapped categories will be listed.' mod='ebay'}">{l s='List on eBay' mod='ebay'}</span>
				</th>
				<th class="center" colspan="2">
					<span data-inlinehelp="{l s='Number of selected products for synchronisation within the category' mod='ebay'}">{l s='Nb selected products' mod='ebay'}</span>
				</th>                
				{*
				<th class="center">
					<span data-dialoghelp="http://pages.ebay.com/help/sell/pictures.html" data-inlinehelp="{l s='By default, only your main photo will appear in your eBay listing. You can add more photos but there may be a charge.' mod='ebay'}">{l s='Photos' mod='ebay'}</span>
				</th>
				*}
			</tr>
		</thead>
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="3">
					<img src="{$_path|escape:'htmlall':'UTF-8'}views/img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>
	
	<div style="margin-top: 5px;">
		<input class="primary button" name="submitSave" type="submit" value="{l s='Save and continue' mod='ebay'}" />
	</div>
</form>
<br>
<div style="display:none" class="warning big tips {if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}">{l s='TIP. You can improve your conversion by XYZ...' mod='ebay'}</div>
<p {if isset($load_cat) && $load_cat == true}class="hidden importCatEbay"{/if}><b>{l s='Warning: Only default product categories are used for the configuration' mod='ebay'}</b></p><br />

<p align="left" class="{if isset($load_cat) && $load_cat == true} hidden importCatEbay{/if}">
	* {l s='In most eBay categories, you can list variations of your products together in one listing called a multi-variation listing, for example a red t-shirt size small, medium and large. In those few categories that don’t support multi-variation listings, a listing will be added for every variation of your product.' mod='ebay'}<br />
	<a href="{l s='http://sellerupdate.ebay.fr/autumn2012/improvements-multi-variation-listings' mod='ebay'}" target="_blank">{l s='Click here for more informations on multi-variation listings' mod='ebay'}</a>
</p><br /><br />

<div {if isset($load_cat) && $load_cat == true}style="display:none"{/if} class="warning big tips h">{l s='Miss some categories ? You may need to upgrade category definitions :' mod='ebay'}
	</br>{l s='- You can compare your category definitions with last available category definitions from eBay using the ' mod='ebay'}<a href="#comparaison" id='link_cat_support'>{l s='comparison tool' mod='ebay'}</a>
	</br>{l s='- If needed, you can upgrade category definition using the ' mod='ebay'}<a id='link_cat_support_reload' href="#resynch">{l s='upgrade tool.' mod='ebay'}</a>
	</br>{l s='New to category definition concept ? Please read ' mod='ebay'} <a class="kb-help" style ="display: inline-block;width: auto;height: 20px;background-image: none;" data-errorcode="{$help_Cat_upd.error_code}" data-module="ebay" data-lang="{$help_Cat_upd.lang}" module_version="{$help_Cat_upd.module_version}" prestashop_version="{$help_Cat_upd.ps_version}" href="" target="_blank">{l s='category definition & reloading article first.' mod='ebay'}</a>

</div>

<script type="text/javascript">
		
	var $selects = false;
	var admin_path = '{$admin_path|escape:'htmlall':'UTF-8'}';
	var module_dir = '{$_module_dir_|escape:'htmlall':'UTF-8'}';
	var ebay_token = '{$configs.EBAY_SECURITY_TOKEN|escape:'htmlall':'UTF-8'}';
	var module_time = '{$date|escape:'htmlall':'UTF-8'}';
	var module_path = '{$_path|escape:'htmlall':'UTF-8'}';
	var id_lang = '{$id_lang|escape:'htmlall':'UTF-8'}';
	var id_ebay_profile = '{$id_ebay_profile|escape:'htmlall':'UTF-8'}';
	var categories_ebay_l = {ldelim}
		'thank you for waiting': "{l s='Thank you for waiting while creating suggestions' mod='ebay'}",
		'no category selected' : "{l s='No category selected' mod='ebay'}",
		'No category found'		 : "{l s='No category found' mod='ebay'}",
		'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
		'Settings updated'		 : "{l s='Settings updated' mod='ebay'}",
		'Unselect products'		: "{l s='Unselect products that you do NOT want to list on eBay' mod='ebay'}",
		'Unselect products clicked' : "{l s='Unselect products that you do NOT want to list on eBay' mod='ebay'}",
        'Products' : "{l s='Products' mod='ebay'}",
        'Stock' : "{l s='Stock' mod='ebay'}",
        'Finish' : "{l s='Finish' mod='ebay'}",
        'An error has occurred' : "{l s='An error has occurred' mod='ebay'}",
        'Waiting' : "{l s='Waiting' mod='ebay'}",
        'categories loaded success' : "{l s='categories loaded successfully.' mod='ebay'}",
        'Download subcategories of' : "{l s='Download subcategories of' mod='ebay'}",
	{rdelim};
    </script>
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/categories.js?date={$date|escape:'htmlall':'UTF-8'}"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function(){
		var form_categories = parseInt("{$form_categories|escape:'htmlall':'UTF-8'}");
		if (form_categories >= 1)
			$("#menuTab2").addClass('success');
		
		else
			$("#menuTab2").addClass('wrong');
		{if isset($load_cat) && $load_cat == true && isset($launch_load_cat) && $launch_load_cat == true}
			loadCategoriesFromEbay();
		{/if}

		$('#link_cat_support').click(function(e){
			e.preventDefault();
			$('#advanced-settings-menu-link').click();
			window.location.href = $(this).attr('href');
		});
		$('#link_cat_support_reload').click(function(e){
			e.preventDefault();
			$('#advanced-settings-menu-link').click();
			window.location.href = $(this).attr('href');
		});

	});
	//]]>
</script>

