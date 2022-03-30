<?php

namespace Vtex\VtexMagento\Api;
use Magento\Framework\Controller\Result\Json;

interface VtexCatalogInterface
{
    /**
     * @return Json
     */
    public function simulation();
}
