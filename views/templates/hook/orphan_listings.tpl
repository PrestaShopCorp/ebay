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
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
    
	<p>
		<b>{l s='The "Orphan eBay Listings" tab shows eBay listings created by this module, that are not synchronised anymore by this module.' mod='ebay'}</b><br/>
		{l s='Reasons for theses listings not be synchronised anymore can be multiple, and are explained in the "Help" column.' mod='ebay'}
	</p>
    
    <form id="orphans-form-view" action="{$show_orphan_listings_url|escape:'htmlall':'UTF-8'}" method="post" class="form">
        <p class="center">
            <input class="primary button" name="submitSave" type="submit" value="{l s='Load orphan listings' mod='ebay'}" />
    	</p>
    </form>
    
    <!-- table -->
       
    <table id="OrphanListings" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				
                <th style="width:110px;">
                    <span>{l s='eBay Listing' mod='ebay'}</span>
				</th>
				
                <th>
					<span>{l s='PrestaShop Product' mod='ebay'}</span>
				</th>
                
				<th class="center">
					<span data-inlinehelp="{l s='Product has been disabled in the PrestaShop product page' mod='ebay'}">{l s='Product Disabled' mod='ebay'}</span>
				</th>
				
                <th class="center">
					<span data-inlinehelp="{l s='Does PrestaShop product have combinations' mod='ebay'}">{l s='Product Combinations' mod='ebay'}</span>
				</th>
                
                <th>
					<span data-inlinehelp="{l s='PrestaShop product default category' mod='ebay'}">{l s='PrestaShop Category' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='eBay category associated with PrestaShop product\'s default category' mod='ebay'}">{l s='eBay Category' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='Does eBay category support multivariation listings ?' mod='ebay'}">{l s='Category Multi-sku' mod='ebay'}</span>
				</th>                

                <th class="center">
					<span data-inlinehelp="{l s='If this column is set to \'no\', product default category has not been synchronised in \'Synchronisation > 1. List products\' tab' mod='ebay'}">{l s='Synchronisation Enabled' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span>{l s='Action' mod='ebay'}</span>
				</th>                
                
                <th class="center">{l s='Help' mod='ebay'}</th>
                
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
    </div>
    <script type="text/javascript">
    
    	var orphan_listings_ebay_l = {ldelim}
    		'Remove this ad?': "{l s='End this listing?' mod='ebay'}"
    	{rdelim};
    </script>
    <script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/orphanListings.js?date={$date|escape:'htmlall':'UTF-8'}"></script>
