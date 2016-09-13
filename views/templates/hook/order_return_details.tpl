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

{if isset($returns[0].id_ebay_order)}
    <div class="row">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-heading">
                    <span class="badge">{l s='eBay return or refund' mod='ebay'}</span>
                </div>

                    <div class="well">
                        <span>{l s='A refund or return has been generated for this order on eBay.' mod='ebay'}</span></br>
                       <span>{l s='- Please log in to your eBay account to manage the refund or return.' mod='ebay'}</span></br>
                       <span>{l s='- Once refund or return terminated, you may manualy update PrestaShop using native butons "Return products" or "Partiel refund".' mod='ebay'}</span></br></br>
                        {foreach from=$returns item=return}
                        <span>{l s='Ebay Order: ' mod='ebay'}{$return.id_ebay_order}</span></br>
                        <span>{l s='Product Name: ' mod='ebay'}{$return.product_name[1]}</span></br>
                        <span>{l s='Description: ' mod='ebay'}{$return.description}</span></br>
                        <span>{l s='Status: ' mod='ebay'}{$return.status}</span></br>
                        <span>{l s='Date: ' mod='ebay'}{$return.date}</span></br></br>
                        {/foreach}

                    </div>

            </div>
        </div>
    </div>
{/if}
