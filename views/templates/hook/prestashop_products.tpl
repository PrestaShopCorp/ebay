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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
    

	<p>
		<b>{l s='The "Prestashop Products" tab displays, for each PrestaShop product, the associated eBay listing.' mod='ebay'}</b><br/>
        {l s='This tab can also help understanding why a PrestaShop product do not generate an eBay listing.' mod='ebay'}
	</p>
    
    <p>
       {l s='The synchronization option (in tab \'Synchronisation > 1. List products\') you have selected is:' mod='ebay'} "{if $ebay_sync_option_resync == 1}{l s='Only synchronise price and quantity' mod='ebay'}{else}{l s='Synchronise everything' mod='ebay'}{/if}"
    </p>
    
    <p>
        {l s='Display' mod='ebay'} : 
        <select id="products-mode">
            <option value="catalogue">{l s='whole PrestaShop catalog' mod='ebay'}</option>
            <option value="on_ebay">{l s='Synchronizable products' mod='ebay'}</option>
        </select>
        
        <input id="products-filter" type="search" placeholder="{l s='Filter products' mod='ebay'}" />
    </p>
    
    <form id="products-form-view" action="{$show_products_url}" method="post" class="form">
        <p class="center">
            <input class="primary button" name="submitSave" type="submit" value="{l s='Load Products' mod='ebay'}" />
    	</p>
    </form>
    
    <!-- pagination -->
    
    <div id="products-pagination-holder"></div>
       
    <!-- table -->
       
    <table id="PrestaShopProducts" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				
                <th style="width:110px;">
                    <span>{l s='ID' mod='ebay'}</span>
				</th>
                
                <th style="width:110px;">
                    <span>{l s='PrestaShop Product' mod='ebay'}</span>
				</th>
				
                <th>
					<span>{l s='Product Stock' mod='ebay'}</span>
				</th>
                
				<th style="width:185px;">
					<span data-inlinehelp="{l s='PrestaShop product default category' mod='ebay'}">{l s='PrestaShop Category' mod='ebay'}</span>
				</th>
				
                <th style="width:185px;">
                    <span data-inlinehelp="{l s='Configured in the \'Parameter > 2.Categories and pricing\' tab' mod='ebay'}">{l s='Associated eBay category' mod='ebay'}</span>
                </th>
                
                <th class="center">
					<span data-inlinehelp="{l s='If this column is set to \'no\', product default category has not been synchronised in \'Synchronisation > 1. List products\' tab' mod='ebay'}">{l s='Synchronisation enabled' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='If this column is set to \'no\', product has been unselected in the \'Parameters > 2.Categories and pricing\' tab' mod='ebay'}">{l s='Product selected' mod='ebay'}</span>
				</th>
                
                <th class="center">
					<span data-inlinehelp="{l s='Display link to associated eBay listing' mod='ebay'}">{l s='eBay listing' mod='ebay'}</span>
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
    
    <script type="text/javascript">
        var products_ebay_l = {ldelim}
    		'Empty stock': "{l s='Empty stock' mod='ebay'}",
    		'Synchronisation disabled' : "{l s='Synchronisation disabled' mod='ebay'}",
    		'Product not selected'		 : "{l s='Product has been unselected from tab \'Parameters > 2. Categories and pricing\'' mod='ebay'}",
            'Link': "{l s='Access to listing' mod='ebay'}",
            'No listing': "{l s='No listing yet' mod='ebay'}",
            'See main product': "{l s='See main product' mod='ebay'}",
        {rdelim};   
        var id_employee = {$id_employee};     
    </script>
    
    <script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/prestaShopProducts.js?date={$date|escape:'htmlall':'UTF-8'}"></script>

</div>