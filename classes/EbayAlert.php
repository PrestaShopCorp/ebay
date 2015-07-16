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
				'message' => $this->ebay->l('You will send more than one image. This can have financial consequences thank you to verify this link'),
				'link_warn' => $link->getPictureUrl()
				);
		}

		if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING') >= 12)
			$this->errors[] = array(
				'type' => 'error', 
				'message' => $this->ebay->l('You can\'t send more of 12 pictures by product. Please configure that in Advanced Parameters')
				);
	}
}