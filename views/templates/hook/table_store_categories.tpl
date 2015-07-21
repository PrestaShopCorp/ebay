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

{if $categoryList === false || sizeof($categoryList) === 0}
	<tr>
		<td colspan="3" class="center">{$noCatFound|escape:'htmlall':'UTF-8'}</td>
	</tr>
{else}
	{foreach from=$categoryList key=k  item=c}
		<tr{if $k % 2 !== 0} class="alt_row"{/if} id="category-{$c.id_category|escape:'htmlall':'UTF-8'}">
			<td>{$c.name|escape:'htmlall':'UTF-8'}
			</td>
			<td id="categoryPath{$c.id_category|escape:'htmlall':'UTF-8'}">
                <select name="store_category[{$c.id_category|escape:'htmlall':'UTF-8'}]" style="font-size: 12px; width: 160px;" class="ebay_select">
                    {foreach from=$eBayStoreCategoryList item=ec}
                        <option value="{$ec.ebay_category_id|escape:'htmlall':'UTF-8'}" {if in_array($c.id_category, $ec.id_categories)}selected="selected"{/if}>{$ec.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
			</td>
		</tr>
	{/foreach}
{/if}