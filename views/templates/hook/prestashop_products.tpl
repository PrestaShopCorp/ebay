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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
    

	<p>
		<b>{l s='The "Prestashop Products" tab displays the status of the products in the categories you have configured.' mod='ebay'} {l s='This tab allows you to check:' mod='ebay'}</b>
	</p>
    
    <ul>
        <li>{l s='The product stock left' mod='ebay'}</li>
        <li>{l s='The affected Prestashop category' mod='ebay'}</li>
        <li>{l s="The eBay category with which it's synchronised" mod='ebay'}</li>
    </ul>
    
    <p>
       {l s='The synchronization option you have selected is:' mod='ebay'} "{if $ebay_sync_option_resync == 1}{l s='Only synchronise price and quantity' mod='ebay'}{else}{l s='Synchronise everything' mod='ebay'}{/if}"
    </p>
    
    <p>
        <select name="">
            <option value="catalogue">{l s='Whole Prestashop catalogue' mod='ebay'}</option>
            <option value="on_ebay">{l s='Products synchronised on eBay' mod='ebay'}</option>
        </select>
        
        <input type="search" />
    </p>
    
    <form action="{$show_products_url}" method="post" class="form">
        <p class="center">
            <input class="primary button" name="submitSave" type="submit" value="{l s='View Products' mod='ebay'}" />
    	</p>
    </form>
    
    <!-- pagination -->
    
    {*
    {if $nb_products > 0}
    	<p id="textPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(($nb_products / 20)|round:"0") mod='ebay'}</p>
    	<ul id="pagination" class="pagination">
    		<li class="prev"><</li>
    		{math equation="floor(x/20)" x=$nb_products assign=nb_pages} 
    		{for $i=1 to ($nb_pages +1)}
    			<li{if $i == 0} class="current"{/if}>{$i}</li>
    		{/for}
    		<li class="next">></li>
    	</ul>
    {/if}
    *}
    
       
    <!-- table -->
       
    <table id="PrestaShopProducts" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				
                <th style="width:110px;">
                    <span data-inlinehelp="{l s='' mod='ebay'}">{l s='PrestaShop Product' mod='ebay'}</span>
				</th>
				
                <th>
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Product Stock' mod='ebay'}</span>
				</th>
                
				<th style="width:185px;">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='PrestaShop Category' mod='ebay'}</span>
				</th>
				
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Corresponding eBay Category' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Synchronising' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Product selected' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='eBay Products' mod='ebay'}</span>
				</th>
                
                <th class="center">{l s='Help' mod='ebay'}</th>
                
			</tr>
		</thead>
        
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="3">
					<img src="{$_path|escape:'htmlall'}img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
        
	</table>    
    
    <script type="text/javascript">
        
    </script>
    <script type="text/javascript" src="{$_module_dir_|escape:'htmlall'}ebay/js/prestaShopProducts.js?date={$date|escape:'htmlall'}"></script>
    
    {*


<script type="text/javascript">
		
	var $selects = false;
	
	var module_dir = '{$_module_dir_|escape:'htmlall'}';
	var ebay_token = '{$configs.EBAY_SECURITY_TOKEN|escape:'htmlall'}';
	var module_time = '{$date|escape:'htmlall'}';
	var module_path = '{$_path|escape:'htmlall'}';
	var id_lang = '{$id_lang|escape:'htmlall'}';
	var id_ebay_profile = '{$id_ebay_profile|escape:'htmlall'}';
	var categories_ebay_l = {ldelim}
		'thank you for waiting': "{l s='Thank you for waiting while creating suggestions' mod='ebay'}",
		'no category selected' : "{l s='No category selected' mod='ebay'}",
		'No category found'		 : "{l s='No category found' mod='ebay'}",
		'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
		'Settings updated'		 : "{l s='Settings updated' mod='ebay'}",
		'Unselect products'		: "{l s='Unselect products that you do NOT want to list on eBay' mod='ebay'}",
		'Unselect products clicked' : "{l s='Unselect products that you do NOT want to list on eBay' mod='ebay'}"
	{rdelim};
    </script>
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall'}ebay/js/categories.js?date={$date|escape:'htmlall'}"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function(){
		var form_categories = parseInt("{$form_categories|escape:'htmlall'}");
		if (form_categories >= 1)
			$("#menuTab2").addClass('success');
		
		else
			$("#menuTab2").addClass('wrong');
	});
	//]]>
</script>
    *}