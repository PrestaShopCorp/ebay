{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
	{if isset($alerts) && !empty($alerts)}
	<div class="warning big">
		{$alerts|escape:'htmlall'}
	</div>
	{/if}
	<p>
		<b>{l s='Select a category' mod='ebay'}</b>
		<br />
		{l s='To list your products on eBay, you need to map your Prestashop category with an eBay category.' mod='ebay'} <br />
		{l s='The button below will automatically map your categories with eBay categories. We recommend you check that you’re happy with the category chosen and amend if necessary.' mod='ebay'}
	</p>
	{if $form_store_categories == 0}

	{/if}
	<!---------------------------->
	<p>
		<b>{l s='List on eBay' mod='ebay'}</b>
		<br />
		{l s='Choose which of your items you want to list on eBay by ticking the box.' mod='ebay'}
	</p>
</div>
<br />

{if $nb_categorie > 0}
	<p id="textStoresPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(($nb_categorie / 20)|round:"0" + 1) mod='ebay'}</p>
	<ul id="stores_pagination">
		<li class="prev"><</li>
		{for $i=0 to ($nb_categorie / 20)|round:"0"}
			<li{if $i == 0} class="current"{/if}>{$i + 1}</li>
		{/for}
		<li class="next">></li>
	</ul>
{/if}

<form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall'}{else}tab={$tab|escape:'htmlall'}{/if}&configure={$configure|escape:'htmlall'}&token={$token|escape:'htmlall'}&tab_module={$tab_module|escape:'htmlall'}&module_name={$module_name|escape:'htmlall'}&id_tab=2&section=store_category" method="post" class="form" id="configFormStoreCategories"><table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
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
					<img src="{$_path|escape:'htmlall'}views/img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>
	<div style="margin-top: 5px;">
		<input class="primary button" name="submitSave" type="submit" value="{l s='Save and continue' mod='ebay'}" />
	</div>
</form>
{*
<br>
<div style="display:none" class="warning big tips">{l s='TIP. You can improve your conversion by XYZ...' mod='ebay'}</div>
<p><b>{l s='Warning: Only default product categories are used for the configuration' mod='ebay'}</b></p><br />

<p align="left">
	* {l s='In most eBay categories, you can list variations of your products together in one listing called a multi-variation listing, for example a red t-shirt size small, medium and large. In those few categories that don’t support multi-variation listings, a listing will be added for every variation of your product.' mod='ebay'}<br />
	<a href="{l s='http://sellerupdate.ebay.fr/autumn2012/improvements-multi-variation-listings' mod='ebay'}" target="_blank">{l s='Click here for more informations on multi-variation listings' mod='ebay'}</a>
</p><br /><br />
*}
<script type="text/javascript">
		
	var $selects = false;
	
	var module_dir = '{$_module_dir_|escape:'htmlall'}';
	var ebay_token = '{$configs.EBAY_SECURITY_TOKEN|escape:'htmlall'}';
	var module_time = '{$date|escape:'htmlall'}';
	var module_path = '{$_path|escape:'htmlall'}';
	var id_lang = '{$id_lang|escape:'htmlall'}';
	var id_ebay_profile = '{$id_ebay_profile|escape:'htmlall'}';
	var ebay_l = {ldelim}
		'no category selected' : "{l s='No category selected' mod='ebay'}",
		'No category found'		 : "{l s='No category found' mod='ebay'}",
		'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
		'Settings updated'		 : "{l s='Settings updated' mod='ebay'}",
	{rdelim};
</script>
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall'}ebay/views/js/storeCategories.js?date={$date|escape:'htmlall'}"></script>