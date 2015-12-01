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

{if $logs === false || sizeof($logs) === 0}

	<tr>
		<td colspan="3" class="center">{$noLogFound|escape:'htmlall':'UTF-8'}</td>
	</tr>

{else}

	{foreach from=$logs key=k  item=log}
		<tr{if $k % 2 !== 0} class="alt_row"{/if}>
			<td>{$log['id_ebay_api_log']|escape:'htmlall':'UTF-8'}</td>
			<td>{$log['id_ebay_profile']|escape:'htmlall':'UTF-8'}</td>
			<td>{$log['type']|escape:'htmlall':'UTF-8'}</td>
			<td>{$log['context']|escape:'htmlall':'UTF-8'}</td>
			<td><a class="show_link" href data-content="{$log['data_sent']|escape:'htmlall':'UTF-8'}">{$showStr|escape:'htmlall':'UTF-8'}</a></td>
			<td><a class="show_link" href data-content="{$log['response']|escape:'htmlall':'UTF-8'}">{$showStr|escape:'htmlall':'UTF-8'}</a></td>
			<td>{if $log['id_product']}{$log['id_product']|escape:'htmlall':'UTF-8'}{/if}</td>
			<td>{if $log['id_order']}{$log['id_order']|escape:'htmlall':'UTF-8'}{/if}</td>
			<td>{$log['date_add']|escape:'htmlall':'UTF-8'}</td>
		</tr>
	{/foreach}
    
    <script type="text/javascript">
        $('.show_link').click(function(event) {
            event.preventDefault();
            var content = $(this).data('content');
           $(this).parent().html(JSON.stringify(content));
        });
    </script>
{/if}