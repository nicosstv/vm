<?php

namespace Vtex\VtexMagento\Controller\Adminhtml\Settings;

use \Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadLog extends Action
{
    protected $request;
    protected $resultRawFactory;
    protected $fileFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->request = $request;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($id = $this->request->getParam('id')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if ($import = $objectManager->create('Vtex\VtexMagento\Model\Import')->load($id)) {
                $filePath = "log". DIRECTORY_SEPARATOR ."import" . DIRECTORY_SEPARATOR . $import->getFilename();
                $content['type'] = 'filename';
                $content['value'] = $filePath;
                $content['rm'] = 0;
                return $this->fileFactory->create($import->getFilename(), $content, DirectoryList::VAR_DIR);
            }
        }
    }
}
