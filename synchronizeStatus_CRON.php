<?php

/*
  This routine will update eBay with the status of orders that were originally fetched from there.

   Copyright (c) 2014 Rob O'Donnell.
   All rights reserved.

   Redistribution and use in source and binary forms, with or without modification, are permitted
   provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of conditions
   and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list of
   conditions and the following disclaimer in the documentation and/or other materials provided
   with the distribution.

   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
   OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
   AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
   CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
   DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
   DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
   WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/ebay.php');




class ebaySynchronizeStatusTask extends Ebay {

	public function __construct() {
		parent::__construct();


		// get all ebay orders where current state is shipped and doesn't match last recorded state
		// Should possibly only do it on change of shipped flag from the status...
		$sql = 'SELECT os.shipped, eoo.id_shop, eoo.id_order, eo.id_ebay_order, eo.id_order_ref, o.current_state,
				c.name AS carrier, oc.tracking_number, DATE_FORMAT(oc.date_add, "%Y-%m-%dT%H:%i:%s.000Z") AS shipping_date
			FROM '._DB_PREFIX_.'ebay_order_order eoo
			LEFT JOIN '._DB_PREFIX_.'ebay_order eo ON eo.id_ebay_order = eoo.id_ebay_order AND eoo.id_ebay_order > 0
			LEFT JOIN '._DB_PREFIX_.'orders o ON eoo.id_order = o.id_order
			LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
			LEFT JOIN '._DB_PREFIX_.'order_carrier oc ON oc.id_order = eoo.id_order
			LEFT JOIN '._DB_PREFIX_.'carrier c ON c.id_carrier = oc.id_carrier

			WHERE eo.id_ebay_order IS NOT NULL AND os.shipped = 1 AND o.current_state != IFNULL(eo.current_state, 0)';

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if ($result) {
			$ebay = new EbayRequest();
			foreach ($result as $order) {
				$result = $ebay->setCompleteSale($order);
				if ($result) {
					Db::getInstance()->execute( 'UPDATE '._DB_PREFIX_.'ebay_order eo
						SET eo.current_state = '.$order['current_state'].'
						WHERE eo.id_ebay_order = '.$order['id_ebay_order']);
				} else
					echo print_r($order,true).'<br>'."\n".$ebay->error;
			}

		}

	}
}

$x = Configuration::getGlobalValue('ROB_EBAY_TABLES');
if (strpos($x, 'order_state') === false) {
	$sql = 'ALTER TABLE '._DB_PREFIX_.'ebay_order ADD COLUMN current_state INT(10)';
	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
//	print_r( $result);
	Configuration::updateGlobalValue('ROB_EBAY_TABLES', $x.'|order_state');
}


new ebaySynchronizeStatusTask();
