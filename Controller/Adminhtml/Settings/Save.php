<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use \Vtex\VtexMagento\Model\SettingsFactory;

class Save extends Action
{
    protected $_pageFactory;
    protected $settingsFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SettingsFactory $settingsFactory)
    {
        $this->_pageFactory = $pageFactory;
        $this->settingsFactory = $settingsFactory;

        return parent::__construct($context);
    }

    /**
     * Booking action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $post = (array)$this->getRequest()->getPost();

        if ($post) {

            $errors = [];

            if (!isset($post['vendor_name']) || !$post['vendor_name']) {
                $errors[] = 'Vendor name is required';
            }

            if (!isset($post['app_key']) || !$post['app_key']) {
                $errors[] = 'App key is required';
            }

            if (!isset($post['app_token']) || !$post['app_token']) {
                $errors[] = 'App token is required';
            }

            if (!isset($post['seller_id']) || !$post['seller_id']) {
                $errors[] = 'Seller ID is required';
            }

            if (!isset($post['catalog_v2'])) {
                $post['catalog_v2'] = 0;
            } else {
                $post['catalog_v2'] = 1;
            }

            if (!isset($post['salesChannel'])) {
                $post['salesChannel'] = '1';
            }

            if (!$errors) {
                try {
                    $model = $this->settingsFactory->create();
                    $model->setData($post);
                    $model->save();
                    $this->messageManager->addSuccessMessage('Settings saved!');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e, __("We can\'t submit your request, Please try again."));
                }
            } else {
                foreach($errors as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
