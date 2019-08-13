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
 
/**
 * Cardpay Solutions HighRisk credit card block.
 *
 * @category Cardpay
 * @package  Cardpay_HighRisk
 * @author   Cardpay Solutions, Inc. <sales@cardpaymerchant.com>
 */
class Cardpay_HighRisk_Block_Creditcard extends Mage_Core_Block_Template
{
    const TYPE_EDIT = 'edit';
    
    /**
     * Returns credit card
     *
     * @return Cardpay_HighRisk_Model_Creditcard credit card object
     */
    public function creditCard()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        $card = Mage::getModel('highrisk/creditcard')->load($id);
        return $card;
    }
    
    /**
     * If existing card being edited
     * 
     * @return bool if existing card edit
     */
    public function isEditMode()
    {
        if ($this->getType() == self::TYPE_EDIT) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns url for add
     * 
     * @return string add url
     */
    public function getAddUrl()
    {
        return $this->getUrl('customer/creditcard/new');
    }
    
    /**
     * Returns url for edit
     * 
     * @param int $id id of credit card
     * 
     * @return string edit url
     */
    public function getEditUrl($id)
    {
        return $this->getUrl('customer/creditcard/edit', array('id' => $id));
    }
    
    /**
     * Returns url for delete
     * 
     * @param int $id id of credit card
     * 
     * @return string delete url
     */
    public function getDeleteUrl($id)
    {
        return $this->getUrl('customer/creditcard/delete', array('id' => $id));
    }
    
    /**
     * Returns url for delete confirm
     * 
     * @param int $id id of credit card
     * 
     * @return string delete confirm url
     */
    public function getDeleteConfirmUrl($id)
    {
        return $this->getUrl('customer/creditcard/deleteconfirm', array('id' => $id));
    }
    
    /**
     * Returns url for new or edit form action
     * 
     * @return string form action url
     */
    public function getFormAction()
    {
        if ($this->getType() == self::TYPE_EDIT) {
            $url = $this->getUrl(
                'customer/creditcard/update', 
                array('id' => Mage::app()->getRequest()->getParam('id'))
            );
        } else {
            $url = $this->getUrl('customer/creditcard/save');
        }
        return $url;
    }
    
    /**
     * Returns page title
     * 
     * @return string page title
     */
    public function getTitle()
    {
        $title = '';
        if ($this->getType() == self::TYPE_EDIT) {
            $title = 'Edit Credit Card';
        } else {
            $title = 'Add Credit Card';
        }
        return $this->__($title);
    }
    
    /**
     * Returns url for back
     * 
     * @return string back url
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/creditcard/index');
    }
    
    /**
     * Returns credit card expire months
     * 
     * @return array expiration months
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, Mage::getSingleton('payment/config')->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }
    
    /**
     * Returns credit card expire years
     * 
     * @return array expiration years
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = Mage::getSingleton('payment/config')->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Returns available credit card types
     * 
     * @return array card types
     */
    public function getCcAvailableTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        if ($method = Mage::getModel('highrisk/paymentmethod')) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }
    
    /**
     * If make default should be shown
     * 
     * @return bool if can make default
     */
    public function canShowMakeDefault()
    {
        $model = Mage::getModel('highrisk/creditcard');
        $cardCount = count($model->currentCustomerCards());
        
        if ($this->getType() == self::TYPE_EDIT) {
            $cardId = $this->getRequest()->getParam('id');
            $card = $model->load($cardId);
            if ($card->getIsDefault()) {
                return false;
            } else {
                return true;
            }  
        } else {
            if ($cardCount > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * If cc cid should be shown
     * 
     * @return bool if cid
     */
    public function hasVerification()
    {
        $method = Mage::getModel('highrisk/paymentmethod');
        return $method->getConfigData('useccv');
    }
}