<?php
/**
 * Cardpay Solutions, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 * 
 * @category  Cardpay
 * @package   Cardpay_HighRisk
 * @copyright Copyright (c) 2015 Cardpay Solutions, Inc. (http://www.cardpaymerchant.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

require_once 'Mage/Customer/controllers/AccountController.php';

/**
 * Cardpay Solutions HighRisk credit card controller.
 *
 * @category Cardpay
 * @package  Cardpay_HighRisk
 * @author   Cardpay Solutions, Inc. <sales@cardpaymerchant.com>
 */
class Cardpay_HighRisk_CreditCardController extends Mage_Customer_AccountController
{
    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Action predispatch
     *
     * Check if extension and vault are enabled, otherwise no route
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)
            || !Mage::getStoreConfig('payment/highrisk/use_vault')
            || !Mage::getStoreConfig('payment/highrisk/active')
        ) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }
    
    /**
     * Customer credit card list
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initMessages();
        $this->renderLayout();
    }
    
    /**
     * Customer new credit card
     */
    public function newAction()
    {
        $this->loadLayout();
        $this->_initMessages();
        $this->renderLayout();
    }
    
    /**
     * Customer save credit card
     */
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_getSession()->addError($this->__('Error saving credit card'));
            return $this->_redirect('customer/creditcard/index');
        }
        if ($this->getRequest()->isPost()) {
            $customerId = $this->_getSession()->getCustomerId();
            $cardholderFirstname = $this->_getSession()->getCustomer()->getFirstname();
            $cardholderLastname = $this->_getSession()->getCustomer()->getLastname();
            $data = $this->getRequest()->getPost();
            
            $card = Mage::getModel('highrisk/creditcard');
            $card->addData($data['payment']);
            $card->setData('customer_id', $customerId);
            $card->setData('cc_last4', substr($card->getCcNumber(), -4));
            $card->setData('cardholder_firstname', $cardholderFirstname);
            $card->setData('cardholder_lastname', $cardholderLastname);
            $highrisk = Mage::getModel('highrisk/paymentMethod');
            try {
                $token = $highrisk->verify($card);
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirect('customer/creditcard/new');
            }
            
            $card->setData('token', $token);
            if ($card->getIsDefault()) {
                $card->clearDefault();
            }
            $card->save();
            $this->_getSession()->addSuccess($this->__('The credit card has been saved.'));
        }
        $this->_redirect('customer/creditcard/index');
    }

    /**
     * Customer update credit card
     */
    public function updateAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_getSession()->addError($this->__('Error updating credit card'));
            return $this->_redirect('customer/creditcard/index');
        }
        if ($this->getRequest()->isPost()) {
            $cardId = $this->getRequest()->getParam('id');
            $customerId = $this->_getSession()->getCustomerId();
            $cardholderFirstname = $this->_getSession()->getCustomer()->getFirstname();
            $cardholderLastname = $this->_getSession()->getCustomer()->getLastname();
            $data = $this->getRequest()->getPost();
            $card = Mage::getModel('highrisk/creditcard')->load($cardId);
            
            // Validate that card belongs to customer
            if ($card->getCustomerId() != $customerId) {
                $this->_getSession()->addError($this->__('The credit card does not belong to this customer.'));
                return $this->_redirect('customer/creditcard/index');
            }
            
            $card->addData($data['payment']);
            $card->setData('customer_id', $customerId);
            $card->setData('cc_last4', substr($card->getCcNumber(), -4));
            $card->setData('cardholder_firstname', $cardholderFirstname);
            $card->setData('cardholder_lastname', $cardholderLastname);
            $highrisk = Mage::getModel('highrisk/paymentMethod');
            try {
                $token = $highrisk->verify($card);
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                return $this->_redirect('customer/creditcard/new');
            }
            
            $card->setData('token', $token);
            if ($card->getIsDefault()) {
                $card->clearDefault();
            }
            $card->save();
            $this->_getSession()->addSuccess($this->__('The credit card has been updated.'));
        }
        $this->_redirect('customer/creditcard/index');
    }
    
    /**
     * Customer edit credit card
     */
    public function editAction()
    {
        $cardId = $this->getRequest()->getParam('id');
        if ($cardId) {
            $card = Mage::getModel('highrisk/creditcard')->load($cardId);
            
            // Validate that card belongs to customer
            if ($card->getCustomerId() != $this->_getSession()->getCustomerId()) {
                $this->_getSession()->addError($this->__('The credit card does not belong to this customer.'));
                return $this->_redirect('customer/creditcard/index');
            }
            $this->loadLayout();
            $this->_initMessages();
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Invalid credit card id.'));
            return $this->_redirect('customer/creditcard/index');
        }
    }
    
    /**
     * Customer delete credit card
     */
    public function deleteAction()
    {
        $cardId = $this->getRequest()->getParam('id');
        if ($cardId) {
            $card = Mage::getModel('highrisk/creditcard')->load($cardId);
            
            // Validate that card belongs to customer
            if ($card->getCustomerId() != $this->_getSession()->getCustomerId()) {
                $this->_getSession()->addError($this->__('The credit card does not belong to this customer.'));
                return $this->_redirect('customer/creditcard/index');
            }
            $this->loadLayout();
            $this->_initMessages();
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Invalid credit card id.'));
            return $this->_redirect('customer/creditcard/index');
        }
    }
    
    /**
     * Customer confirm delete credit card
     */
    public function deleteConfirmAction()
    {
        $cardId = $this->getRequest()->getParam('id');
        if ($cardId) {
            $card = Mage::getModel('highrisk/creditcard')->load($cardId);
            
            // Validate that card belongs to customer
            if ($card->getCustomerId() != $this->_getSession()->getCustomerId()) {
                $this->_getSession()->addError($this->__('The credit card does not belong to this customer.'));
                return $this->_redirect('customer/creditcard/index');
            }

            try {
                $card->delete();
                $this->_getSession()->addSuccess($this->__('The credit card has been deleted.'));
                return $this->_redirect('customer/creditcard/index');
            } catch (Exception $e){
                $this->_getSession()->addError($this->__('An error occurred while deleting the credit card.'));
                return $this->_redirect('customer/creditcard/index');
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid credit card id.'));
            return $this->_redirect('customer/creditcard/index');
        }
    }
    
    /**
     * Init layout messages, add page title
     */
    protected function _initMessages()
    {
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Credit Cards'));
    }
}