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

{if $products === false || sizeof($products) === 0}

	<tr>
		<td colspan="3" class="center">{$noProductFound|escape:'htmlall':'UTF-8'}</td>
	</tr>

{else}

    {if $nbProducts > $nbPerPage}
        <div id="products-pagination" style="display:none">
            <p id="textPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(floor($nbProducts / $nbPerPage)|round:"0") mod='ebay'}</p>
            <ul id="pagination" class="pagination">
                <li class="prev"><</li>
                {math equation="floor(x/$nbPerPage)" x=$nbProducts assign=nb_pages} 
                {for $i=1 to ($nb_pages)}
                    <li{if $i == $p} class="current"{/if}>{$i}</li>
                {/for}
                <li class="next">></li>
            </ul>
        </div>
    {/if}

	{foreach from=$products key=k  item=p}

		<tr{if $k % 2 !== 0} class="alt_row"{/if} id="product-{$p.id_product|escape:'htmlall':'UTF-8'}">

            <td>
                {if $p.hasAttributes}
                    <a id="show-vars-{$p.id_product|escape:'htmlall':'UTF-8'}"
                        class="show-vars"
                        product-name="{$p.name|escape:'htmlall':'UTF-8'}"
                        multi-sku="{if $p.EbayCategoryIsMultiSku}1{else}0{/if}"
                        sync="{if $p.sync}1{else}0{/if}"
                        blacklisted="{if $p.blacklisted}1{else}0{/if}"
                     href="javascript:showVariations({$p.id_product|escape:'htmlall':'UTF-8'})">&#9654;</a>
                     <a href="{$p.link}" target="_blank">{$p.id_product|intval}</a>
                {else}<span class="left-padded-name"><a href="{$p.link}" target="_blank">{$p.id_product|intval}</a></span>{/if}
            </td>

			<td>{$p.name|escape:'htmlall':'UTF-8'}</td>

            <td class="center{if !$p.stock} red{/if}">{$p.stock}</td>
            
            <td>{$p.category_full_name|escape:'htmlall':'UTF-8'}</td>

            {if $p.id_category_ref}
                <td>{$p.ebay_category_full_name|escape:'htmlall':'UTF-8'}</td>
            {else}
                <td class="center">-</td>
            {/if}
            
            <td class="center">{if $p.sync}{l s='Yes' mod='ebay'}{else}<span class="red">{l s='No' mod='ebay'}</span>{/if}</td>

            <td class="center">{if $p.id_category_ref && !$p.blacklisted}{l s='Yes' mod='ebay'}{else}<span class="red">{l s='No' mod='ebay'}</span>{/if}</td>
            
            <td>
                {if $p.id_category_ref && !$p.EbayCategoryIsMultiSku && $p.hasAttributes && !$p.EbayProductRef}
                    {l s='Non multi-sku category' mod='ebay'}
                {elseif $p.id_category_ref && !$p.EbayCategoryIsMultiSku && $p.hasAttributes && $p.EbayProductRef}
                    {l s='Several ads' mod='ebay'}
                {elseif !$p.id_category_ref || !$p.EbayProductRef}
                    {l s='No listing yet' mod='ebay'}                  
                {else}
                    <a href="{if $p.EbayProductRef}{$p.link}{/if}" target="_blank">{l s='Access to listing' mod='ebay'}</a>    
                {/if}
            </td>
            
            <td>
                {if !$p.stock && $p.sync && !$p.blacklisted}
                    {l s='Empty stock' mod='ebay'}
                {elseif !$p.is_category_active}
                    {l s='This category is disabled in Prestashop, however the synchronisation will go on' mod='ebay'}
                {elseif $p.stock && $p.id_category_ref && !$p.sync && !$p.blacklisted}
                    {l s='Product default category has not been synchronised in \'Synchronisation > 1. List products\' tab' mod='ebay'}
                {elseif $p.stock && $p.sync && $p.blacklisted}
                   {l s='Product has been unselected from tab \'Parameters > 2. Categories and pricing\'' mod='ebay'}
                {elseif $p.stock && $p.sync && !$p.blacklisted && !$p.EbayProductRef}
                    {l s='Category is not synchronised, or an issue occured during synchronisation of this product' mod='ebay'}
                {elseif $p.id_category_ref && !$p.EbayCategoryIsMultiSku && $p.hasAttributes && $p.EbayProductRef}                        {l s='eBay category is not multi sku' mod='ebay'}
                {elseif $p.id_category_ref && (!$p.stock || !$p.sync || $p.blacklisted)}
                    {l s='More than one reason' mod='ebay'}
                {/if}
            </td>
            
	{/foreach}

{/if}