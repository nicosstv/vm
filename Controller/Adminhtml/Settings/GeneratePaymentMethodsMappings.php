<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

use Vtex\VtexMagento\Api\Vtex;
use \Vtex\VtexMagento\Model\SettingsFactory;
use Vtex\VtexMagento\Model\PaymentMethodsMappingFactory;
use Vtex\VtexMagento\Model\PaymentMethodsMapping;

class GeneratePaymentMethodsMappings extends Action
{
    protected $_pageFactory;
    protected $settingsFactory;
    protected $mapping;
    protected $vClient;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SettingsFactory $settingsFactory,
        PaymentMethodsMapping $mapping,
        Vtex $vClient
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->settingsFactory = $settingsFactory;
        $this->mapping = $mapping;
        $this->vClient = $vClient;

        return parent::__construct($context);
    }

    public function execute()
    {
        if ($vtexPaymentMethods = $this->vClient->getAllPaymentMethods()) {
            foreach ($vtexPaymentMethods as $item) {
                $this->mapping->insert($item);
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
