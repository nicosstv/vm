<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

use Vtex\VtexMagento\Api\VtexCatalogV2;
use \Vtex\VtexMagento\Model\SettingsFactory;
use Vtex\VtexMagento\Model\MappingFactory;
use Vtex\VtexMagento\Model\Mapping;

class GenerateCategoriesMappings extends Action
{
    protected $_pageFactory;
    protected $settingsFactory;
    protected $mapping;
    protected $catalogV2;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SettingsFactory $settingsFactory,
        Mapping $mapping,
        VtexCatalogV2 $catalogV2
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->settingsFactory = $settingsFactory;
        $this->mapping = $mapping;
        $this->catalogV2 = $catalogV2;

        return parent::__construct($context);
    }

    public function execute()
    {
        if ($vtexCategories = $this->catalogV2->getAllCategories()) {
            foreach ($vtexCategories['roots'] as $item) {
                $this->mapping->insertRecursive($item);
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
