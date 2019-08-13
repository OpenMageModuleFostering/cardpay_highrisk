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
 * Cardpay Solutions HighRisk data helper.
 *
 * @category Cardpay
 * @package  Cardpay_HighRisk
 * @author   Cardpay Solutions, Inc. <sales@cardpaymerchant.com>
 */
class Cardpay_HighRisk_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns credit card name
     * 
     * @param $code credit card type code
     * 
     * @return string card type name
     */
    public function getCcTypeName($code)
    {
        $ccTypes = array(
            'VI' => 'Visa',
            'MC' => 'MasterCard',
            'AE' => 'American Express',
            'DI' => 'Discover',
            'JCB' => 'JCB'
        );
        return $ccTypes[$code];
    }
    
    /**
     * Returns avs response description
     * 
     * @param $code avs response code
     * 
     * @return string avs message
     */
    public function getAvsResponse($code)
    {
        $avsResponses = array(
            'X' => 'Exact match, 9-character numeric ZIP',
            'Y' => 'Exact match, 5-character numeric ZIP',
            'D' => 'Exact match, 5-character numeric ZIP',
            'M' => 'Exact match, 5-character numeric ZIP',
            'A' => 'Address match only',
            'B' => 'Address match only',
            'W' => '9-character numeric ZIP match only',
            'Z' => '5-character ZIP match only',
            'P' => '5-character ZIP match only',
            'L' => '5-character ZIP match only',
            'N' => 'No address or ZIP match only',
            'C' => 'No address or ZIP match only',
            'U' => 'Address unavailable',
            'G' => 'Non-U.S. issuer does not participate',
            'I' => 'Non-U.S. issuer does not participate',
            'R' => 'Issuer system unavailable',
            'E' => 'Not a mail/phone order',
            'S' => 'Service not supported',
            'O' => 'AVS not available'
        );
        if (array_key_exists($code, $avsResponses)) {
            return $avsResponses[$code];
        } else {
            return '';
        }
    }

    /**
     * Returns cvv response description
     * 
     * @param $code cvv response code
     * 
     * @return string cvv message
     */
    public function getCvvResponse($code)
    {
        $cvvResponses = array(
            'M' => 'CVV2/CVC2 match',
            'N' => 'CVV2/CVC2 no match',
            'P' => 'Not processed',
            'S' => 'Merchant has indicated that CVV2/CVC2 is not present on card',
            'U' => 'Issuer is not certified and/or has not provided Visa encryption keys'
        );
        if (array_key_exists($code, $cvvResponses)) {
            return $cvvResponses[$code];
        } else {
            return '';
        }
    }
}