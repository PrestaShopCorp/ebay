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

<div>
	
    
    {if $has_ebay_shop}
    
        {if $has_store_categories}
        	<p>
        		<b>{l s='Select a category' mod='ebay'}</b>
        		<br />
        		{l s='To list your products on eBay, please map your Prestashop categories with your eBay shop categories.' mod='ebay'} <br />
        		{l s='This mapping has no effect on “Categories and pricing” tab.' mod='ebay'}
                <br />
                {l s='If you do not map any category below, products will all appear in an “Other” category in your eBay shop.' mod='ebay'}   
        	</p>
        {else}
            <p>
                <b><a href="http://cgi6.sandbox.ebay.fr/ws/eBayISAPI.dll?StoreCategoryMgmt" target="_blank">{l s="you don’t have any category in your shop, please refer to this page to create categories" mod='ebay'}</a></b>
            </p> 
        {/if}
    {else}
        <div class="ebay_mind big">
        	<p>
        		<b>{l s='Your eBay account has no eBay shop registered.' mod='ebay'} </b>
            
        		<a href="{$ebay_store_url}" target="_blank">{l s="An eBay shop subscription isn’t required but you may benefit. Find out if an eBay Shop is right for you." mod='ebay'}</a>
        	</p>
        </div>    
    {/if}

</div>
<br />

{if $nb_categorie > 0}
	<p id="textStoresPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(($nb_categorie / 20)|round:"0") mod='ebay'}</p>
	<ul id="stores_pagination" class="pagination">
		<li class="prev"><</li>
		{math equation="floor(x/20)" x=$nb_categorie assign=nb_pages} 
		{for $i=1 to ($nb_pages +1)}
			<li{if $i == 0} class="current"{/if}>{$i}</li>
		{/for}
		<li class="next">></li>
	</ul>
{/if}

<form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=10&section=store_category" method="post" class="form" id="configFormStoreCategories"><table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th style="width:110px;">
					{l s='PrestaShop category' mod='ebay'}
				</th>
				<th>
					<span data-inlinehelp="{l s='Only products with a mapped category will be listed.' mod='ebay'}">{l s='eBay store category' mod='ebay'}</span>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="2">
					<img src="{$_path|escape:'htmlall':'UTF-8'}views/img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>
	<div style="margin-top: 5px;">
		<input class="primary button" name="submitSave" type="submit" value="{l s='Save and continue' mod='ebay'}" />
        </form>
        <form method="post" style="float: left" action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=10&section=store_category">
            <input type="hidden" name="refresh_store_cat" value="1" />
            <input class="button" type="submit" value="{l s='Reload store categories' mod='ebay'}" />
        </form>
	</div>
    
    {if $not_compatible_store_categories}
        <div class="warning big tips">
            {l s='The following categories are not available for they contain subcategories. The eBay API does not permit sending products in these categories: ' mod='ebay'} {$not_compatible_store_categories}
        </div>
    {/if}

<script type="text/javascript">
		
	var $selects = false;
	
	var module_dir = '{$_module_dir_|escape:'htmlall':'UTF-8'}';
	var ebay_token = '{$configs.EBAY_SECURITY_TOKEN|escape:'htmlall':'UTF-8'}';
	var module_time = '{$date|escape:'htmlall':'UTF-8'}';
	var module_path = '{$_path|escape:'htmlall':'UTF-8'}';
	var id_lang = '{$id_lang|escape:'htmlall':'UTF-8'}';
	var id_ebay_profile = '{$id_ebay_profile|escape:'htmlall':'UTF-8'}';
	var store_categories_ebay_l = {ldelim}
		'No category found'		 : "{l s='No category found' mod='ebay'}",
		'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
		'Settings updated'		 : "{l s='Settings updated' mod='ebay'}",
	{rdelim};
</script>
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/storeCategories.js?date={$date|escape:'htmlall':'UTF-8'}"></script>
