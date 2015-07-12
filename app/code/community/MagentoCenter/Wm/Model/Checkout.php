<?php

class MagentoCenter_Wm_Model_Checkout extends Mage_Payment_Model_Method_Abstract {

    protected $_code          = 'wm';
    protected $_formBlockType = 'wm/form';
    protected $_infoBlockType = 'wm/info';


    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('wm/redirect', array('_secure' => true));
    }

    public function getWebmoneyUrl() {
		$url = 'http://bangladeshbrand.com/dbblpay/payment.php';
        return $url;
    }

    public function getQuote() {

        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);		        
        return $order;
    }

    public function getWebmoneyCheckoutFormFields() {



        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $amount   = trim(round($order->getGrandTotal(), 2));
		
		$name = Mage::getSingleton('customer/session')->getCustomer()->getName();
		$customer_id = Mage::getSingleton('customer/session')->getCustomer()->getCustomerId();
		$email =  Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		
		
		
		/*
		*   set order id in session
		*/
		
		
		$_SESSION['specialy_order_id_odd'] = $order_id;
		
		/*
		*	Put the values for desier domain and redirect url
		*	domain name is the domain name from which is going to check out	
		*	redirect url is the url to redirect	
		*/
		
		$payment_method = 'dutch';
		
		
		$domain_name = 'http://bangladeshbrands.com';
		$redirect_url = 'http://bangladeshbrands.com/wm/redirect/success';


        $params = array(
	
		'paymnet_method' => $payment_method,
		'name' 			 => $name,
		'email' 		 => $email,
		'customer_id'  	 => $customer_id,
		'domain' 		 => $domain_name,
		'red_url' 		 => $redirect_url,
		'transaction_id' => $order_id,
		'amount' 		 => $amount 
		
			
        );
        return $params;


    }
}
