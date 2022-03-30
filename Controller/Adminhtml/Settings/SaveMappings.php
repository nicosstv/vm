<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class SaveMappings extends Action
{
    public function __construct(Context $context)
    {
        return parent::__construct($context);
    }

    /**
     * Booking action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($post = (array)$this->getRequest()->getPost()) {
            foreach ($post['mapping'] as $id => $mapping) {
                $map = $this->_objectManager->create(\Vtex\VtexMagento\Model\Mapping::class)->load($id);
                if ($map->getId()) {
                    $map->setMagentoId(!empty($mapping) ? (int)$mapping : null)->save();
                }
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
