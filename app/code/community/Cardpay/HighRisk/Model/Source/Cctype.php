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
 * Cardpay Solutions HighRisk credit card type source model.
 *
 * @category Cardpay
 * @package  Cardpay_HighRisk
 * @author   Cardpay Solutions, Inc. <sales@cardpaymerchant.com>
 */
class Cardpay_HighRisk_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    /**
     * Allowed credit card types
     * 
     * @return array allowed card types
     */
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'JCB');
    }
}