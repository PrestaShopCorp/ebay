<?php
/**
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
 */

class EbayAlert
{
	private $ebay_profile;
	private $ebay;
	private $errors		= array();
	private $warnings	= array();
	private $infos		= array();

	private $alerts;

	public function __construct(Ebay $obj){
		$this->ebay = $obj;
		$this->ebay_profile = $obj->ebay_profile;

	}

	public function getAlerts(){
		$this->checkNumberPhoto();
		$this->checkOrders();

		$this->build();

		return $this->alerts;
	}

	private function build(){
		$this->alerts = array_merge($this->errors, $this->warnings, $this->infos);
	}

	private function checkNumberPhoto(){
		if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING') > 0){
			$link = new EbayCountrySpec();
			$link->getPictureUrl();
			$this->warnings[] = array(
				'type' => 'warning', 
				'message' => $this->ebay->l('You will send more than one image. This can have financial consequences. Please verify this link'),
				'link_warn' => $link->getPictureUrl()
				);
		}

		if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING') >= 12)
			$this->errors[] = array(
				'type' => 'error', 
				'message' => $this->ebay->l('You can\'t send more than 12 pictures by product. Please configure that in Advanced Parameters')
				);
	}

	private function checkOrders(){
		$this->checkOrdersCountry();
	}

	private function checkOrdersCountry(){
		$countries = EbayOrderErrors::getEbayOrdersCountry();
		$list = array('country' => '', 'order' => '');

		foreach ($countries as $key => $orders) {
			
			$country = new Country(Country::getByIso($key), (int)Configuration::get('PS_LANG_DEFAULT'));
			
			if ($country->active)
				continue;

			Tools::isEmpty($list['country']) ? ($list['country'] .= $country->name) : ($list['country'] .= ', '.$country->name);

			foreach ($orders as $order)
				Tools::isEmpty($list['order']) ? ($list['order'] .= $order['id_order_seller']) : ($list['order'] .= ', '.$order['id_order_seller']);
		}
		
		$this->errors[] = array(
			'type' => 'error', 
			'message' => $this->ebay->l('You must enable the following countries : ').$list['country'].$this->ebay->l('. In order to import this eBay order(s) : ').$list['order'].'.',
				);
	}

	public function sendDailyMail(){
		$this->getAlerts();

		$template_vars = array(
			'{errors}' 	=> $this->formatErrorForEmail(),
			'{warnings}' 	=> $this->formatWarningForEmail(),
			'{infos}' 	=> $this->formatInfoForEmail(),
		);

		Mail::Send(
			(int)Configuration::get('PS_LANG_DEFAULT'),
			'ebayAlert',
			Mail::l('Recap of your eBay module', (int)Configuration::get('PS_LANG_DEFAULT')),
			$template_vars,
			strval(Configuration::get('PS_SHOP_EMAIL')),
			null,
			strval(Configuration::get('PS_SHOP_EMAIL')),
			strval(Configuration::get('PS_SHOP_NAME')),
			null,
			null,
			dirname(__FILE__).'/../views/templates/mails/'
		);
	}

	public function formatErrorForEmail(){
		$html = '<tr>
					<td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
						<table style="width:100%">
							<tbody>
								<tr>
									<td width="10" style="padding:7px 0">&nbsp;</td>
									<td style="padding:7px 0">
										<font size="2" face="Open-sans, sans-serif" color="#555454">
											<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">';

		$html .= $this->ebay->l('Erreur(s)');

		$html .= 							'</p>';

		foreach ($this->errors as $key => $error) {
			$html .=	'<p style="color:#333;padding-bottom:10px;';

			if (array_key_exists($key+1, $this->errors))
				$html .= 'border-bottom:1px solid #d6d4d4;';

			$html .= '">
							<strong>'.$error['message'].'</strong>
						</p>';
		}

		$html .= '
										
									</font>
								</td>
								<td width="10" style="padding:7px 0">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0!important">&nbsp;</td>
			</tr>';

		return $html;
	}

	public function formatWarningForEmail(){
		$html = '<tr><td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
					<table style="width:100%">
						<tbody>
							<tr>
								<td width="10" style="padding:7px 0">&nbsp;</td>
								<td style="padding:7px 0">
									<font size="2" face="Open-sans, sans-serif" color="#555454">
										<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">';

		$html .= $this->ebay->l('Warning(s)');

		$html .= 						'</p>';

		foreach ($this->warnings as $key => $warning) {
			$html .=	'<p style="color:#333;padding-bottom:10px;';

			if (array_key_exists($key+1, $this->warnings))
				$html .= 'border-bottom:1px solid #d6d4d4;';

			$html .= '">
							<strong>'.$warning['message'].'</strong>
						</p>';
		}

		$html .= '
										
									</font>
								</td>
								<td width="10" style="padding:7px 0">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0!important">&nbsp;</td>
			</tr>';

		return $html;
	}

	public function formatInfoForEmail(){
		$html = '<tr><td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
					<table style="width:100%">
						<tbody>
							<tr>
								<td width="10" style="padding:7px 0">&nbsp;</td>
								<td style="padding:7px 0">
									<font size="2" face="Open-sans, sans-serif" color="#555454">
										<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">';

		$html .= $this->ebay->l('Information(s)');

		$html .= 						'</p>';

		foreach ($this->infos as $key => $info) {
			$html .=	'<p style="color:#333;padding-bottom:10px;';

			if (array_key_exists($key+1, $this->infos))
				$html .= 'border-bottom:1px solid #d6d4d4;';

			$html .= '">
							<strong>'.$info['message'].'</strong>
						</p>';
		}

		$html .= '
										
									</font>
								</td>
								<td width="10" style="padding:7px 0">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0!important">&nbsp;</td>
			</tr>';

		return $html;
	}

}