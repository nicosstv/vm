<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vtex\VtexMagento\Api;
use Magento\Framework\Controller\Result\Json;

/**
 * Provides metadata about an attribute.
 *
 * @api
 */
interface OrderInterface
{

    /**
     * @param int $id
     * @return Json
     */
    public function getOrder($id);

    /**
     * @return Json
     */
    public function createOrder();

    /**
     * @return Json
     */
    public function cancelOrder();

    /**
     * @return Json
     */
    public function fulfilOrder();
}
