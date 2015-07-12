<?php


class MagentoCenter_Wm_RedirectController extends Mage_Core_Controller_Front_Action {

    protected $_order;

    protected function _expireAjax() 
	{
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function indexAction() 
	{	
		/*	save cutomer redirect status 	*/
	    $session = Mage::getSingleton('checkout/session');
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        $order->addStatusToHistory($order->getStatus(), Mage::helper('wm')->__('Customer was redirected to DBBL Gateway.'));
        $order->save();
		$this->getResponse()
                ->setHeader('Content-type', 'text/html; charset=utf8')
                ->setBody($this->getLayout()
                ->createBlock('wm/redirect')
                ->toHtml());
    }

    public function successAction() 
	{
		$dbbl_transaction_id = $this->getRequest()->getParam('dbbl_transaction_id'); 
		$amount = $this->getRequest()->getParam('amount');
		$transaction_status = trim($this->getRequest()->getParam('transaction_status'));
		$card_no = $this->getRequest()->getParam('card_no');
		$approval_code = $this->getRequest()->getParam('approval_code');
		$transaction_id = $this->getRequest()->getParam('transaction_id');
		$result_code = $this->getRequest()->getParam('result_code');
		
		// get data
		 $order_id = $transaction_id;//$_SESSION['specialy_order_id_odd'];
        if(!empty($dbbl_transaction_id) && !empty($transaction_id)  ) 
		{
			
			//if($orderId==$transaction_id){
			if($transaction_id)
			{
			
			$order = Mage::getModel('sales/order');
			$order->loadByIncrementId($transaction_id);
			$date    =  date("l F d, Y, h:i:s");
			
			/* save checkout status in database	*/
				if($result_code=="000")
				{
					$invoice = $order->prepareInvoice();
					//$invoice->register()->capture();
					Mage::getModel('core/resource_transaction')
						->addObject($invoice)
						->addObject($invoice->getOrder())
						->save();
					$invoice->sendEmail(true, '');//sending invoice to customer[25/09/13]
					$order->addStatusToHistory($order->getStatus(), $transaction_id);//saving the transaction id 
					$order->addStatusToHistory($order->getStatus(), $dbbl_transaction_id);
					$order->addStatusToHistory($order->getStatus(), $transaction_status);
					$order->addStatusToHistory($order->getStatus(), $card_no);
					$order->addStatusToHistory($order->getStatus(), Mage::helper('wm')->__('Customer successfully returned from DBBL'));
					
					$status = true;
				} else 
				{
			   
				$order->cancel();
				$order->addStatusToHistory($order->getStatus(), $transaction_id);//saving the transaction id 
				$order->addStatusToHistory($order->getStatus(), $dbbl_transaction_id);
				$order->addStatusToHistory($order->getStatus(), $transaction_status);
				$order->addStatusToHistory($order->getStatus(), $card_no);
				$order->addStatusToHistory($order->getStatus(), Mage::helper('wm')->__('Customer was rejected by DBBL'));
				$status = false;
				Mage::getSingleton('core/session')->addError('The payment could not be completed.');

				}

				$order->save();						
				$session = Mage::getSingleton('checkout/session');
				$session->setQuoteId($transaction_id);				
				Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
				if($status){
					$this->_redirect('checkout/onepage/success', array('_secure'=>true));
				} else{
					$this->_redirect('checkout/onepage/failure');
				}
			}//fails to get order id
        		
		}else {
            //$this->_redirect('*/*/failure');
			$this->_redirect('checkout/onepage/failure');
        }
        
    }
	
	
	
	public function failureAction()
    {

		if (!Mage::getSingleton('core/session')->getError()) {
            $this->norouteAction();
            return;
        }

        $this->getCheckout()->clear();
        $this->loadLayout();
        $this->renderLayout();
    }

}

?>
