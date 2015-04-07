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
		<b>{l s='The "Orphan eBay Ads" tab shows eBay ads that are not connected to a PrestaShop product or a PrestaShop category anymore.' mod='ebay'}</b>
	</p>
    
    <form id="orphans-form-view" action="{$show_orphan_ads_url}" method="post" class="form">
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
       
    <table id="OrphanAds" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				
                <th style="width:110px;">
                    <span data-inlinehelp="{l s='' mod='ebay'}">{l s='eBay Ad' mod='ebay'}</span>
				</th>
				
                <th>
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='PrestaShop Product' mod='ebay'}</span>
				</th>
                
				<th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Product Disabled' mod='ebay'}</span>
				</th>
				
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Multi-sku Product' mod='ebay'}</span>
				</th>
                
                <th>
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='PrestaShop Category' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='eBay Category' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Synchronisation Enabled' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Category Multi-sku' mod='ebay'}</span>
				</th>                
                
                <th class="center">
					<span data-inlinehelp="{l s='' mod='ebay'}">{l s='Action' mod='ebay'}</span>
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
    
    	var orphan_ads_ebay_l = {ldelim}
    		'Remove this ad?': "{l s='Remove this ad?' mod='ebay'}"
    	{rdelim};
    </script>
    <script type="text/javascript" src="{$_module_dir_|escape:'htmlall'}ebay/js/orphanAds.js?date={$date|escape:'htmlall'}"></script>