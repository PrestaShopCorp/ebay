{*
* 2007-2013 PrestaShop
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
        <b>{l s='The "refunds and returns" tab displays all refunds and returns created on eBay and associated with an order imported in PrestaShop.' mod='ebay'}</b><br/>

    </p>


    <!-- table -->

    <table id="OrderReturns" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
        <tr class="nodrag nodrop">

            <th style="width:110px;">
                <span>{l s='PrestaShop Order' mod='ebay'}</span>
            </th>

            <th style="width:110px;">
                <span>{l s='eBay Order' mod='ebay'}</span>
            </th>

            <th class="center">
                <span>{l s='Discription' mod='ebay'}</span>
            </th>

            <th class="center">
                <span>{l s='Status' mod='ebay'}</span>
            </th>


            <th class="center">
                <span>{l s='Type' mod='ebay'}</span>
            </th>

            <th class="center">
                <span>{l s='Date' mod='ebay'}</span>
            </th>

            <th class="center">
                <span>{l s='Id Product' mod='ebay'}</span>
            </th>


        </tr>
        </thead>

        <tbody>
        {if empty($returns)}
        <tr id="removeRow">
            <td class="center" colspan="6">
                <img src="{$_path|escape:'htmlall':'UTF-8'}views/img/loading-small.gif" alt="" />   Aucun!
            </td>
        </tr>
        {/if}
        {foreach from=$returns item="return"}
            <tr>
                <td>{$return.id_order|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.id_ebay_order|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.description|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.status|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.type|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.date|escape:'htmlall':'UTF-8'}</td>
                <td>{$return.id_item|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
        </tbody>

    </table>
</div>
