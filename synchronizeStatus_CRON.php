<?php


include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/ebay.php');




class ebaySynchronizeStatusTask extends Ebay {

	public function __construct() {
		parent::__construct();
//		$this->cronOrdersSync();


		// get all ebay orders where current state is shipped and doesn't match last recorded state
		// (Yes I know this will flag ebay as shipped again if going Shipped->Delivered, but they'll ignore that..)
		$sql = 'SELECT eoo.id_shop, eoo.id_order, eo.id_ebay_order, eo.id_order_ref, o.current_state
			FROM '._DB_PREFIX_.'ebay_order_order eoo
			LEFT JOIN '._DB_PREFIX_.'ebay_order eo ON eo.id_ebay_order = eoo.id_ebay_order and eooo.id_ebay_order > 0
			LEFT JOIN '._DB_PREFIX_.'orders o ON eoo.id_order = o.id_order
			LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
			WHERE os.shipped = 1 AND o.current_state != ifnull(eo.current_state, 0)';

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
//		print_r($result);

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
	print_r( $result);
	Configuration::updateGlobalValue('ROB_EBAY_TABLES', $x.'|order_state');
}


new ebaySynchronizeStatusTask();