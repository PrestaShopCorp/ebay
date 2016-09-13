{*
* 2007-2016 PrestaShop
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

{if isset($payment_profile_id)}
    <SellerProfiles>
        <SellerPaymentProfile>
            <PaymentProfileID>{$payment_profile_id}</PaymentProfileID>
            <PaymentProfileName>{$payment_profile_name}</PaymentProfileName>
        </SellerPaymentProfile>

        <SellerReturnProfile>
            <ReturnProfileID>{$return_profile_id}</ReturnProfileID>
            <ReturnProfileName>{$return_profile_name}</ReturnProfileName>
        </SellerReturnProfile>
        <SellerShippingProfile>
            <ShippingProfileID>{$shipping_profile_id}</ShippingProfileID>
            <ShippingProfileName>{$shipping_profile_name}</ShippingProfileName>
        </SellerShippingProfile>


    </SellerProfiles>
{else}
<ReturnPolicy>
    <ReturnsAcceptedOption>{$returns_accepted_option|escape:'htmlall':'UTF-8'}</ReturnsAcceptedOption>
    <Description><![CDATA[{$description|ebayHtml}]]></Description>
    <ReturnsWithinOption>{$within|escape:'htmlall':'UTF-8'}</ReturnsWithinOption>
    <ShippingCostPaidByOption>{$whopays|escape:'htmlall':'UTF-8'}</ShippingCostPaidByOption>
</ReturnPolicy>
{/if}