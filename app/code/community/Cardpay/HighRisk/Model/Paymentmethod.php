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
 * Cardpay Solutions HighRisk payment method model.
 *
 * @category Cardpay
 * @package  Cardpay_HighRisk
 * @author   Cardpay Solutions, Inc. <sales@cardpaymerchant.com>
 */
class Cardpay_HighRisk_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'highrisk';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canRefundInvoicePartial = true;

    protected $_formBlockType = 'highrisk/form';
    protected $_infoBlockType = 'highrisk/info';

    /**
     * Validate data
     * 
     * @return bool true
     */
    public function validate()
    {
        return true;
    }

    /**
     * Authorizes specified amount
     * 
     * @param Varien_Object $payment payment object
     * @param decimal       $amount  amount in decimals
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $post = Mage::app()->getRequest()->getPost();
        $payload = $this->getPayload($payment, $amount, "auth");
        $response = $this->postTransaction($payload);
        if ($response['response'] == 1) {
            $payment->setTransactionId($response['transactionid'])
                ->setCcApproval($response['authcode'])
                ->setCcTransId($response['transactionid'])
                ->setIsTransactionClosed(0)
                ->setParentTransactionId(null)
                ->setCcAvsStatus(Mage::helper('highrisk')->getAvsResponse($response['avsresponse']))
                ->setCcCidStatus(Mage::helper('highrisk')->getCvvResponse($response['cvvresponse']));
            if (isset($post['payment']['save_card'])) {
                $this->saveCard($payment, $response['customer_vault_id']);
            }
            return $this;
        } else {
            Mage::throwException('Transaction Failed: ' . $response['responsetext']);
        }
    }

    /**
     * Captures specified amount
     * 
     * @param Varien_Object $payment payment object
     * @param decimal       $amount  amount in decimals
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getParentTransactionId()) {
            $payload = $this->getPayload($payment, $amount, "capture");
            $response = $this->postTransaction($payload);
            if ($response['response'] == 1) {
                $payment->setTransactionId($response['transactionid'])
                    ->setCcApproval($response['authcode'])
                    ->setCcTransId($response['transactionid'])
                    ->setIsTransactionClosed(1)
                    ->setParentTransactionId($payment->getParentTransactionId());
                return $this;
            } else {
                Mage::throwException('Capture Failed: ' . $response['responsetext']);
            }
        } else {
            return $this->purchase($payment, $amount);
        }
    }

    /**
     * Authoirzes and captures specified amount
     * 
     * @param Varien_Object $payment payment object
     * @param decimal       $amount  amount in decimals
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function purchase(Varien_Object $payment, $amount)
    {
        $post = Mage::app()->getRequest()->getPost();
        $payload = $this->getPayload($payment, $amount, "sale");
        $response = $this->postTransaction($payload);
        if ($response['response'] == 1) {
            $payment->setTransactionId($response['transactionid'])
                ->setCcApproval($response['authcode'])
                ->setCcTransId($response['transactionid'])
                ->setIsTransactionClosed(1)
                ->setParentTransactionId(null)
                ->setCcAvsStatus(Mage::helper('highrisk')->getAvsResponse($response['avsresponse']))
                ->setCcCidStatus(Mage::helper('highrisk')->getCvvResponse($response['cvvresponse']));
            if (isset($post['payment']['save_card'])) {
                $this->saveCard($payment, $response['customer_vault_id']);
            }
            return $this;
        } else {
            Mage::throwException('Transaction Failed: ' . $response['responsetext']);
        }
    }

    /**
     * Refunds specified amount
     * 
     * @param Varien_Object $payment payment object
     * @param decimal       $amount  amount in decimals
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if ($payment->getParentTransactionId()) {
            $payload = $this->getPayload($payment, $amount, "refund");
            $response = $this->postTransaction($payload);
            if ($response['response'] == 1) {
                $payment->setTransactionId($response['transactionid'])
                    ->setCcApproval($response['authcode'])
                    ->setCcTransId($response['transactionid'])
                    ->setIsTransactionClosed(1)
                    ->setParentTransactionId($payment->getParentTransactionId());
                return $this;
            } else {
                Mage::throwException('Refund Failed: ' . $response['responsetext']);
            }
        } else {
            Mage::throwException('Refund Failed: Invalid parent transaction ID.');
        }
    }

    /**
     * Voides authorized transaction
     * 
     * @param Varien_Object $payment payment object
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function void(Varien_Object $payment)
    {
        if ($payment->getParentTransactionId()) {
            $amount = $payment->getBaseAmountAuthorized();
            $payload = $this->getPayload($payment, $amount, "void");
            $response = $this->postTransaction($payload);
            if ($response['response'] == 1) {
                $payment->setTransactionId($response['transactionid'])
                    ->setCcApproval($response['authcode'])
                    ->setCcTransId($response['transactionid'])
                    ->setIsTransactionClosed(1)
                    ->setParentTransactionId($payment->getParentTransactionId());
                return $this;
            } else {
                Mage::throwException('Void Failed: ' . $response['responsetext']);
            }
        } else {
            Mage::throwException('Void Failed: Invalid parent transaction ID.');
        }
    }

    /**
     * Voides transaction on cancel action
     * 
     * @param Varien_Object $payment payment object
     * 
     * @return Cardpay_HighRisk_Model_PaymentMethod payment method object
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }
    
    /**
     * Requests token for card
     * 
     * @param Cardpay_HighRisk_Model_Creditcard $card credit card object
     * 
     * @return string token value
     */
    public function verify($card)
    {
        $payload = $this->getTokenPayload($card);
        $response = $this->postTransaction($payload);
        if ($response['response'] == 1) {
            return $response['customer_vault_id'];
        } else {
            Mage::throwException('Card Declined: ' . $response['responsetext']);
        }
    }

    /**
     * Saves card and transarmor token
     * 
     * @param Varien_Object $payment payment object
     * @param string        $token   token value
     * 
     * @return Cardpay_HighRisk_Model_Creditcard credit card object
     */
    public function saveCard(Varien_Object $payment, $token)
    {
        if ($token) {
            $customerId = $payment->getOrder()->getCustomerId();
            $card = Mage::getModel('highrisk/creditcard');
            $card->setData('customer_id', $customerId);
            $card->setData('token', $token);
            $card->setData('cc_last4', substr($payment->getCcNumber(), -4));
            $card->setData('cc_exp_month', $payment->getCcExpMonth());
            $card->setData('cc_exp_year', $payment->getCcExpYear());
            $card->setData('cc_type', $payment->getCcType());
            if (count($card->currentCustomerCards()) || count($card->adminCustomerCards())) {
                $card->setData('is_default', '0');
            } else {
                $card->setData('is_default', '1');
            }
            $card->save();
        }
    }

    /**
     * Returns a previously saved card
     * 
     * @param string $token token value
     * 
     * @return Cardpay_HighRisk_Model_Creditcard credit card object
     */
    public function getSavedCard($token)
    {
        $card = Mage::getModel('highrisk/creditcard')->load($token);
        return $card;
    }

    /**
     * Returns payload for transaction
     * 
     * @param Varien_Object $payment          payment object
     * @param decimal       $amount           amount in decimals
     * @param string        $transaction_type transaction type string
     * 
     * @return array payload
     */
    public function getPayload(Varien_Object $payment, $amount, $transactionType)
    {
        $post = Mage::app()->getRequest()->getPost();

        if (isset($post['payment']['token']) && !empty($post['payment']['token'])) {
            $card = $this->getSavedCard($post['payment']['token']);
            $payment->setCcExpYear($card->getCcExpYear())
                ->setCcExpMonth($card->getCcExpMonth())
                ->setCcType($card->getCcType())
                ->setCcLast4($card->getCcLast4());
        }
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();
        $billing = $order->getBillingAddress();
        $yr = substr($payment->getCcExpYear(), -2);
        $expDate = sprintf('%02d%02d', $payment->getCcExpMonth(), $yr);
        $testMode = $this->getConfigData('test_mode');
        $data = '';

        if ($testMode) {
            $username = 'demo';
            $password = 'password';
        } else {
            $username = $this->getConfigData('username');
            $password = $this->getConfigData('password');
        }

        if ($transactionType == "auth" || $transactionType == "sale") {
            if (isset($card)) {
                $data = array(
                    'username' => $this->processInput($username),
                    'password' => $this->processInput($password),
                    'type' => $this->processInput($transactionType),
                    'customer_vault_id' => $this->processInput($card->getToken()),
                    'amount' => number_format($amount, 2, '.', ''),
                    'currency' => 'USD',
                    'orderid' => $this->processInput($orderId),
                    'firstname' => $this->processInput($billing->getFirstname()),
                    'lastname' => $this->processInput($billing->getLastname()),
                    'address1' => $this->processInput($billing->getStreet(1)),
                    'zip' => $this->processInput($billing->getPostcode()),
                    'tax' => number_format($order->getTaxAmount(), '2', '.', ''),
                    'shipping' => number_format($order->getShippingAmount(), '2', '.', ''),
                    'ponumber' => $this->processInput($orderId)
                );
            } else {
                $data = array(
                    'username' => $this->processInput($username),
                    'password' => $this->processInput($password),
                    'type' => $this->processInput($transactionType),
                    'ccnumber' => $this->processInput($payment->getCcNumber()),
                    'ccexp' => $this->processInput($expDate),
                    'amount' => number_format($amount, 2, '.', ''),
                    'currency' => 'USD',
                    'cvv' => $this->processInput($payment->getCcCid()),
                    'orderid' => $this->processInput($orderId),
                    'firstname' => $this->processInput($billing->getFirstname()),
                    'lastname' => $this->processInput($billing->getLastname()),
                    'address1' => $this->processInput($billing->getStreet(1)),
                    'zip' => $this->processInput($billing->getPostcode()),
                    'tax' => number_format($order->getTaxAmount(), '2', '.', ''),
                    'shipping' => number_format($order->getShippingAmount(), '2', '.', ''),
                    'ponumber' => $this->processInput($orderId)
                );
                if (isset($post['payment']['save_card'])) {
                    $data['customer_vault'] = 'add_customer';
                }
            }
        } else {
            $data = array(
                'username' => $this->processInput($username),
                'password' => $this->processInput($password),
                'transactionid' => $this->processInput($payment->getParentTransactionId()),
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => 'USD',
                'type' => $this->processInput($transactionType)
            );
        }
        $query = '';
        foreach ($data as $key => $value) {
            $query .= $key.'='.urlencode($value).'&';
        }
        $query = trim($query, '&');
        return $query;
    }

    /**
     * Returns payload for token request
     * 
     * @param Cardpay_HighRisk_Model_Creditcard $card credit card object
     * 
     * @return array payload
     */
    public function getTokenPayload($card)
    {
        $yr = substr($card->getCcExpYear(), -2);
        $expDate = sprintf('%02d%02d', $card->getCcExpMonth(), $yr);
        $testMode = $this->getConfigData('test_mode');
        $data = '';

        if ($testMode) {
            $username = 'demo';
            $password = 'password';
        } else {
            $username = $this->getConfigData('username');
            $password = $this->getConfigData('password');
        }

        $data = array(
            'username' => $this->processInput($username),
            'password' => $this->processInput($password),
            'type' => 'validate',
            'ccnumber' => $this->processInput($card->getCcNumber()),
            'ccexp' => $this->processInput($expDate),
            'cvv' => $this->processInput($card->getCcCid()),
            'firstname' => $this->processInput($card->getCardholderFirstname()),
            'lastname' => $this->processInput($card->getCardholderLastname()),
            'amount' => '0.00',
            'customer_vault' => 'add_customer'
        );
        $query = '';
        foreach ($data as $key => $value) {
            $query .= $key.'='.urlencode($value).'&';
        }
        $query = trim($query, '&');
        return $query;
    }

    /**
     * Post transaction to gateway
     * 
     * @param array $payload payload
     * @param array $headers headers
     * 
     * @return string json response
     */
    public function postTransaction($payload)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, 'https://cardpaysolutions.transactiongateway.com/api/transact.php');
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($request, CURLOPT_TIMEOUT, 15);
        curl_setopt($request, CURLOPT_VERBOSE, 0);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($request, CURLOPT_NOPROGRESS, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 0);
        $data = curl_exec($request);
        if (false === $data) {
            Mage::throwException('Transaction Error: ' . curl_error($request));
        }
        curl_close($request);
        unset($request);
        $data = explode('&', $data);
        $count = count($data);
        $response = array();
        for ($i = 0; $i < $count; $i++) {
            $rdata = explode('=', $data[$i]);
            $response[$rdata[0]] = $rdata[1];
        }
        return $response;
    }

    /**
     * Returns processed input
     * 
     * @param string $data input data
     * 
     * @return string processed input
     */
    public function processInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return strval($data);
    }
    
    /**
     * If payment method is available for currency
     * 
     * @param string $currencyCode order currency
     * 
     * @return bool available for currency or not
     */
    public function canUseForCurrency($currencyCode)
    {
        if ($currencyCode != 'USD') {
            return false;
        }
        return true;
    }
}