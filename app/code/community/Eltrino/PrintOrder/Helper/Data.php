<?php

class Eltrino_PrintOrder_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_GUESTORDER_AVAILABILITY_PERIOD = 3600; // 1 hour (60 seconds * 60 minutes)

    /**
     * Create GuestOrder object from Order object
     *
     * @param Mage_Sales_Model_Order $order
     * @return Eltrino_PrintOrder_Model_Guestorder
     * @throws Eltrino_PrintOrder_Exception
     */
    public function createFromOrder(Mage_Sales_Model_Order $order)
    {
        if (!$order->getId()) {
            throw new Eltrino_PrintOrder_Exception('Can not create GuestOrder from given Order');
        }

        $guestOrder = Mage::getModel('eltrino_printorder/guestorder');
        $guestOrder->setHash($this->_generateHashForGuestOrder($order))
            ->setOrderId($order->getId())
            ->setExpiredAt($this->_generateExpiredAtForGuestOrder());
        return $guestOrder;
    }

    /**
     * Generate hash string for GuestOrder using Order values
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _generateHashForGuestOrder(Mage_Sales_Model_Order $order)
    {
        return md5($order->getIncrementId());
    }

    /**
     * Generate expired at date for GuestOrder
     *
     * @return int
     */
    protected function _generateExpiredAtForGuestOrder()
    {
        return date('Y-m-d H:i:s', (time() + self::DEFAULT_GUESTORDER_AVAILABILITY_PERIOD));
    }

    /**
     * Check if GuestOrder is still active for guest
     *
     * @param Eltrino_PrintOrder_Model_Guestorder $guestOrder
     * @return bool
     */
    public function getIsGuestOrderActive(Eltrino_PrintOrder_Model_Guestorder $guestOrder)
    {
        if ($guestOrder->getId() && strtotime($guestOrder->getExpiredAt()) > time()) {
            return true;
        }
        return false;
    }

    /**
     * Check if order can be viewed
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function canViewOrder(Mage_Sales_Model_Order $order)
    {
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if (in_array($order->getState(), $availableStates, true)) {
            return true;
        }
        return false;
    }
}
