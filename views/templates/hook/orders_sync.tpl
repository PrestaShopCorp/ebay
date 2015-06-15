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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

	<fieldset style="margin-top:10px;">
		
        <legend>{l s='Synchronize' mod='ebay'}</span></legend>

		<label>
			{l s='Manually Sync Orders' mod='ebay'}
		</label>
		
        <div class="margin-form">
			
			<a href="{$url|escape:'urlencode'}&EBAY_SYNC_ORDERS=1"><input type="button" class="button" value="{l s='Sync Orders from eBay' mod='ebay'}" /></a>
	        <br>
		
        </div>
        
	</fieldset>
    <br>
    <p>
        {l s='Orders are automatically retrieved from eBay every 30 minutes. You can immediately retrieve them by clicking the button below.' mod='ebay'}
    </p>
    <p>
        {l s='If you wish to retrieve orders using a cron task, please go to the “Advanced parameters” tab.' mod='ebay'}
    </p>