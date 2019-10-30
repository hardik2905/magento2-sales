<?php
/**
 * EternityRose.Sales
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.mamis.com.au/licencing
 *
 * @category   EternityRose Sales
 * @copyright  Copyright (c) 2016 EternityRose.Sales Pty Ltd (http://www.mamis.com.au)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.mamis.com.au/licencing
 */

namespace EternityRose\Sales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderPlaceAfter implements ObserverInterface
{
    protected $_helper;

    public function __construct (
        \EternityRose\Sales\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function execute(Observer $observer)
    {
        // If the module is not active, stop processing
        if (!$this->_helper->isActive()) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();

        // If the order is equal to order greater than the threshold
        if ($order->getBaseGrandTotal() >= $this->_helper->getHoldThreshold()) {
            // Attempt to place the order on hold
            try {
                $order->hold();
                $order->addStatusHistoryComment('This order has been placed on hold, as it exceeds the current transaction amount threshold. Please review this order with TER Staff')
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(false);
            }
            catch (\Exception $e) {
                $order->addStatusHistoryComment('This order is above the transaction amount threshold, but was unable to be automatically placed on hold in the system. Please review this order with TER Staff')
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(false);
            }
        }

        return $this;
    }
}
