<?php

namespace Vtex\VtexMagento\Model;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Vtex\VtexMagento\Api\VtexCatalogInterface;
use Vtex\VtexMagento\Helpers\ProductHelper;

class VtexCatalog implements VtexCatalogInterface
{
    protected $request;
    protected $restRequest;
    protected $productHelper;
    protected $logger;

    public function __construct(
        RequestInterface $request,
        Request $restRequest,
        ProductHelper $productHelper,
        LoggerInterface $logger
    )
    {
        $this->request = $request;
        $this->restRequest = $restRequest;
        $this->productHelper = $productHelper;
        $this->logger = $logger;
    }

    /**
     * @return Json
     */
    public function simulation()
    {
        $request = json_decode($this->restRequest->getContent(), true);
        $this->logger->debug('Simulation request: ' . $this->restRequest->getContent());

        $output = [
            'country' => $request['country'],
            'postalCode' => str_replace('-', '', $request['postalCode']),
            'geoCoordinates' => $request['geoCoordinates'],
            'pickupPoints' => [],
            'messages' => [],
            'items' => $this->productHelper->getRequestProducts($request),
            'logisticsInfo' => $this->productHelper->getLogisticsInfo($request)
        ];

        $this->logger->debug('Simulation response: ' . json_encode($output));
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode($output));
    }
}
