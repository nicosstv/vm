<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

use \Vtex\VtexMagento\Model\SettingsFactory;

class ImportCategories extends Action
{
    protected $_pageFactory;
    protected $settingsFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SettingsFactory $settingsFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->settingsFactory = $settingsFactory;

        return parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $path = $directory->getRoot();
        exec("php {$path}/bin/magento vtex:categories > /dev/null 2>&1 &", $out);

        if (count($out) === 1) {
            $this->messageManager->addErrorMessage($out[0]);
        } else {
            $this->messageManager->addSuccessMessage('Categories import started!');
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
