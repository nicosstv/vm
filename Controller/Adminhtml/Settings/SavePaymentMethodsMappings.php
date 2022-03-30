<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class SavePaymentMethodsMappings extends Action {
    public function __construct(Context $context){
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
            foreach ($post['payment_methods_mapping'] as $id => $mapping) {
                $map = $this->_objectManager->create(\Vtex\VtexMagento\Model\PaymentMethodsMapping::class)->load($id);

                if ($map->getId()) {
                    $map->setMagentoName(!empty($mapping) ? explode('{{*}}', $mapping)[1] : null)
                        ->setMagentoGroupName(!empty($mapping) ? explode('{{*}}', $mapping)[0] : null)
                        ->save();
                }
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
