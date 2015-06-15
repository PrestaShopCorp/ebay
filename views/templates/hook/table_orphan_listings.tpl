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

{if $ads === false || sizeof($ads) === 0}

	<tr>
		<td colspan="3" class="center">{l s='No orphan listing' mod='ebay'}</td>
	</tr>

{else}

	{foreach from=$ads key=k  item=a}

		<tr{if $k % 2 !== 0} class="alt_row"{/if}>

			<td>
                {if $a.id_product_ref}
                    <a style="display: block;width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis" href="{$a.link|escape:'htmlall':'UTF-8'}" target="_blank">{$a.link|escape:'htmlall':'UTF-8'}</a>
                {/if} 
			</td>

            <td>{if $a.exists}{$a.psProductName|escape:'htmlall':'UTF-8'}{else}{l s='Product deleted. Id: ' mod='ebay'}{$a.id_product}{/if}</td>
            
            {if $a.exists}
                <td class="center">{if $a.active && !$a.blacklisted}{l s='No' mod='ebay'}{else}{l s='Yes' mod='ebay'}{/if}</td>
            {else}
                <td class="center">-</td>
            {/if}                
            
            {if $a.exists}
                <td class="center">{if $a.isMultiSku}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}</td>
            {else}
                <td class="center">-</td>
            {/if}                
            
            {if $a.exists}
                <td>{$a.category_full_name}</td>
            {else}
                <td class="center">-</td>
            {/if}                

            {if $a.exists && $a.id_category_ref}
                <td>{$a.ebay_category_full_name}</td>
            {else}
                <td class="center">-</td>
            {/if}
            
            {if $a.exists && $a.id_category_ref}
                <td class="center">{if $a.EbayCategoryIsMultiSku}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}</td>
            {else}
                <td class="center">-</td>
            {/if}

            {if $a.exists && $a.id_category_ref}
                <td class="center">{if $a.sync}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}</td>
            {else}
                <td class="center">-</td>
            {/if}
            
            <td class="center">
                <a href="#" class="delete-orphan" ref="{$a.id_product_ref}"><img src="../img/admin/delete.gif" /></a>
            </td>
            
            <td>
                {if !$a.exists}
                    {l s='PrestaShop Product does not exists' mod='ebay'}
                {/if}
            </td>
            
	{/foreach}

{/if}