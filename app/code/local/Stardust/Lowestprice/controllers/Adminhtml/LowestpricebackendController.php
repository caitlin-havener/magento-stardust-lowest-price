<?php
class Stardust_Lowestprice_Adminhtml_LowestpricebackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Lowest Price History"));
	   $this->renderLayout();
    }
}