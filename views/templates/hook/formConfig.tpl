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

{if isset($green_message) && $green_message}
    <div class="module_confirmation conf confirm settings-menu menu-msg">{$green_message|escape:'htmlall':'UTF-8'}</div>
{/if}

{if $parametersValidator.indicator == "success" 
	&& $itemSpecificValidator.indicator == "success"
	&& $shippingValidator.indicator == "success"
	&& $templateValidator.indicator == "success"}
	<div class="bootstrap">
		{if isset($cron_task.products) && $cron_task.products.is_active == 1}
			{if $cron_task.products.last_sync == 'none'}
				<div class="alert alert-warning">
					{l s='The product cron job has never been run.' mod='ebay'}
				</div>
			{elseif $cron_task.products.last_sync.warning_long_since}
				<div class="alert alert-danger">
					{l s='Last product synchronization has been done the ' mod='ebay'}
					{$cron_task.products.last_sync.date}
					{l s=' at ' mod='ebay'}
					{$cron_task.products.last_sync.time}
					{l s=' and it tried to synchronize ' mod='ebay'}
					{$cron_task.products.last_sync.nb_products}
					{l s=' products' mod='ebay'}
				</div>
			{else}
				<div class="alert alert-info">
					{l s='Last product synchronization has been done the ' mod='ebay'}
					{$cron_task.products.last_sync.date}
					{l s=' at ' mod='ebay'}
					{$cron_task.products.last_sync.time}
					{l s=' and it tried to synchronize ' mod='ebay'}
					{$cron_task.products.last_sync.nb_products}
					{l s=' products' mod='ebay'}
				</div>
			{/if}
			
		{/if}
		{if isset($cron_task.orders) && $cron_task.orders.is_active == 1}
			{if $cron_task.orders.last_sync == 'none'}
				<div class="alert alert-warning">
					{l s='Order cron job has never been run.' mod='ebay'}
				</div>
			{else}
				<div class="alert alert-{$cron_task.orders.alert}">
					{l s='Last order synchronization has been done the ' mod='ebay'}
					{$cron_task.orders.last_sync.date}
					{l s=' at ' mod='ebay'}
					{$cron_task.orders.last_sync.time}
				</div>
			{/if}
			
		{/if}
	</div>
{/if}
<ul class="settings-menu menuTab">
    
	<li id="menuTab1" class="menuTabButton selected {$parametersValidator.indicator|escape:'htmlall':'UTF-8'}">1. {l s='Account settings' mod='ebay'}</li>
    
	<li id="menuTab2" class="menuTabButton {$categoryValidator.indicator|escape:'htmlall':'UTF-8'}">2. {l s='Categories and pricing' mod='ebay'}</li>
    
	<li id="menuTab8" class="menuTabButton {$itemSpecificValidator.indicator|escape:'htmlall':'UTF-8'}">3. {l s='Item specifics' mod='ebay'}</li>
    
    <li id="menuTab10" class="menuTabButton success">4. {l s='Store Categories' mod='ebay'}</li>
    
	<li id="menuTab3" class="menuTabButton {$shippingValidator.indicator|escape:'htmlall':'UTF-8'}">5. {l s='Dispatch and Shipping' mod='ebay'}</li>
    
	<li id="menuTab4" class="menuTabButton {$templateValidator.indicator|escape:'htmlall':'UTF-8'}">6. {l s='Template manager' mod='ebay'}</li>    
    
</ul>


<ul class="sync-menu menuTab ebay_hidden">
    
	<li id="menuTab5" class="menuTabButton ">1. {l s='List products' mod='ebay'}</li>
	
    <li id="menuTab14" class="menuTabButton ">2. {l s='Orders Synchronization' mod='ebay'}</li>

</ul>


<ul class="visu-menu menuTab ebay_hidden">
    
    <li id="menuTab15" class="menuTabButton">1. {l s='PrestaShop Products' mod='ebay'}</li>
    
    <li id="menuTab9" class="menuTabButton">2. {l s='Ebay Listings' mod='ebay'}</li>
        
    <li id="menuTab16" class="menuTabButton">3. {l s='Orphan Listings' mod='ebay'}</li>
    
    <li id="menuTab6" class="menuTabButton">4. {l s='Order history' mod='ebay'}</li>
	
    <li id="menuTab11" class="menuTabButton">5. {l s='API Logs' mod='ebay'}</li>
	
    <li id="menuTab12" class="menuTabButton">6. {l s='Order Logs' mod='ebay'}</li>
    
</ul>


<ul class="advanced-settings-menu menuTab ebay_hidden">
    <li id="menuTab13" class="menuTabButton">1. {l s='Advanced Settings' mod='ebay'}</li>    
</ul>

<div id="tabList" class="{$class_general|escape:'htmlall':'UTF-8'}">
	<div id="menuTab1Sheet" class="tabItem selected">{if isset($parametersValidator.message)}<div class="ebay_{$parametersValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$parametersValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_parameters}</div>
	<div id="menuTab13Sheet" class="tabItem selected">{if isset($parametersValidator.message)}<div class="ebay_{$parametersValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$parametersValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_advanced_parameters}</div>
	<div id="menuTab2Sheet" class="tabItem">{if isset($categoryValidator.message)}<div class="ebay_{$categoryValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$categoryValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_category}</div>
	<div id="menuTab8Sheet" class="tabItem">{if isset($itemSpecificValidator.message)}<div class="ebay_{$itemSpecificValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$itemSpecificValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_items_specifics}</div>
	<div id="menuTab3Sheet" class="tabItem">{if isset($shippingValidator.message)}<div class="ebay_{$shippingValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$shippingValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_shipping}</div>
	<div id="menuTab4Sheet" class="tabItem">{if isset($templateValidator.message)}<div class="ebay_{$templateValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$templateValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_template_manager}</div>
	<div id="menuTab5Sheet" class="tabItem">{if isset($listingValidator.message)}<div class="ebay_{$listingValidator.indicatorBig|escape:'htmlall':'UTF-8'} big">{$listingValidator.message|escape:'htmlall':'UTF-8'}</div>{/if}{$form_ebay_sync}</div>
	<div id="menuTab9Sheet" class="tabItem">{$ebay_listings}</div>
    <div id="menuTab10Sheet" class="tabItem">{$form_store_category}</div>
	<div id="menuTab11Sheet" class="tabItem">{$api_logs}</div>
	<div id="menuTab12Sheet" class="tabItem">{$order_logs}</div>
	<div id="menuTab6Sheet" class="tabItem">{$orders_history}</div>
	<div id="menuTab14Sheet" class="tabItem">{$orders_sync}</div>
    <div id="menuTab15Sheet" class="tabItem">{$ps_products}</div>
	<div id="menuTab16Sheet" class="tabItem">{$orphan_listings}</div>
</div>
<br clear="left" />
<br />
{*
<script>
	{literal}
	$(".menuTabButton").click(function () {
		$(".menuTabButton.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#" + this.id + "Sheet").addClass("selected");
	});
	{/literal}
</script>
{if $id_tab}
	<script>
		$(".menuTabButton.selected").removeClass("selected");
		$("#menuTab{$id_tab|escape:'htmlall':'UTF-8'}").addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#menuTab{$id_tab|escape:'htmlall':'UTF-8'}Sheet").addClass("selected");
	</script>
{/if}
*}


<div id="helpertexts" style="display:none;">
	<div id="returnsAccepted" style="width:300px">
		{l s='All sellers on eBay must specify a returns policy for their items, whether your policy is to accept returns or not. If you don\'t specify a returns policy, eBay will select a default returns policy for you.' mod='ebay'}
	</div>
	<div id="dispatchTime" style="width:300px">
		{l s='The dispatch time is the time between the buyer’s payment clearing and you sending the item. Buyers are increasingly expecting short dispatch times, ideally next day, but preferably within 3 working days. ' mod='ebay'}
	</div>
	<div id="DomShipp" style="width:300px">
		{l s='To add a shipping method, map your PrestaShop options with one offered by eBay.' mod='ebay'}
	</div>
	<div id="tagsTemplate" style="width:300px">
		{ldelim}MAIN_IMAGE{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_1{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_2{rdelim}<br/>
		{ldelim}MEDIUM_IMAGE_3{rdelim}<br/>
		{ldelim}PRODUCT_PRICE{rdelim}<br/>
		{ldelim}PRODUCT_PRICE_DISCOUNT{rdelim}<br/>
		{ldelim}DESCRIPTION_SHORT{rdelim}<br/>
		{ldelim}DESCRIPTION{rdelim}<br/>
		{ldelim}FEATURES{rdelim}<br/>
		{ldelim}EBAY_IDENTIFIER{rdelim}<br/>
		{ldelim}EBAY_SHOP{rdelim}<br/>
		{ldelim}SLOGAN{rdelim}<br/>
		{ldelim}PRODUCT_NAME{rdelim}
	</div>
	<div id="categoriesProgression" style="overflow: auto;width: 200px;height: 100px;text-align: center;font-size: 16px;padding-top: 30px;"></div>
</div>
