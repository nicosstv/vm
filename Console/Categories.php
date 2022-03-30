<?php
namespace Vtex\VtexMagento\Console;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Api\VtexCatalogV2;
use Vtex\VtexMagento\Model\Import;
use Vtex\VtexMagento\Model\ResourceModel\Import\CollectionFactory as ImportCollectionFactory;
use Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class Categories extends Command
{
    protected $vClient;
    protected $catalogV2;
    protected $import;
    protected $_importCollectionFactory = null;
    protected $_settingsCollectionFactory = null;
    protected $settings;

    public function __construct(
        Vtex $vClient,
        VtexCatalogV2 $catalogV2,
        Import $import,
        ImportCollectionFactory $importCollectionFactory,
        SettingsCollectionFactory $settingsCollectionFactory
    )
    {
        $this->vClient = $vClient;
        $this->catalogV2 = $catalogV2;
        $this->import = $import;
        $this->_importCollectionFactory = $importCollectionFactory;
        $this->_settingsCollectionFactory = $settingsCollectionFactory;
        parent::__construct();
        $this->initLogger();

        $settingsCollection = $this->_settingsCollectionFactory->create();
        $settingsCollection->addFieldToSelect('*')->load();
        if($settingsCollection->getItems()) {
            if ($settings = $settingsCollection->getFirstItem()) {
                $this->settings = $settings;
            }
        }
    }

    protected function configure()
    {
        $this->setName('vtex:categories');
        $this->setDescription('Import categories into VTEX marketplace');

        parent::configure();
    }

    protected function initLogger()
    {
        if (!file_exists(BP . "/var/log/import")) {
            mkdir(BP . "/var/log/import",775, true);
        }
    }

    protected function getLogger($logFile)
    {
        $writer = new \Laminas\Log\Writer\Stream(BP . "/var/log/import/{$logFile}");
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($writer);

        return $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importCollection = $this->_importCollectionFactory->create();
        $importCollection->addFieldToSelect('*')
            ->addFieldToFilter('type', array('like' => 'Categories import'))
            ->addFieldToFilter('status', array('like' => 'In progress'))
            ->load();

        if (!$importCollection->count()) {

            $objectManager = ObjectManager::getInstance();
            $categories = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')
                ->create();

            $categories->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', '1');

            //trigger import start
            $progress = 0;
            $errors = 0;

            $this->import
                ->setData([
                    'type' => 'Categories import',
                    'status' => 'In progress',
                    'total' => count($categories),
                    'progress' => $progress,
                    'errors' => $errors,
                    'date' => date('Y-m-d H:i:s')
                ])->save();

            $logFileName = "category-import-{$this->import->getId()}.txt";
            $logger = $this->getLogger($logFileName);

            $i = 1;
            foreach ($categories as $category) {
                if ($this->settings->getCatalogV2()) {
                    if (!$response = $this->createVTEXCategory($category->getData())) {
                        $response = "Category [{$category->getId()}] `{$category->getName()}` successfully imported!";
                    } else {
                        $errors++;
                    }
                } else {
                    if (!$this->vClient->getCategoryById($category->getId())) {
                        if (!$response = $this->vClient->createCategory($category->getData())) {
                            $response = "Category [{$category->getId()}] `{$category->getName()}` successfully imported!";
                        } else {
                            $errors++;
                        }
                    } else {
                        if (!$response = $this->vClient->updateCategory($category->getData())) {
                            $response = "Category [{$category->getId()}] `{$category->getName()}` successfully updated!";
                        } else {
                            $errors++;
                        }
                    }
                }

                $progress++;
                $this->import
                    ->addData(['progress' => $progress, 'errors' => $errors])
                    ->save();

                $i++;
                $logger->info($response);
            }

            $this->import
                ->addData([
                    'status' => 'Finished',
                    'filename' => $logFileName
                ])
                ->save();
        }

    }

    public function createVTEXCategory($category)
    {
        $vtexCategories = $this->catalogV2->getAllCategories();

        if (!$this->catalogV2->recursiveCategorySearch($category['name'], $vtexCategories['roots'])) {

            if ($category['parent_id'] == 1) {
                return $this->catalogV2->createCategory($category);
            } else {
                $objectManager = ObjectManager::getInstance();
                $info = $objectManager->create('Magento\Catalog\Model\Category')->load($category['parent_id']);
                if ($parent_id = $this->catalogV2->recursiveCategorySearch($info['name'], $vtexCategories['roots'])) {
                    $category['parent_id'] = $parent_id;
                    return $this->catalogV2->createCategory($category);
                }
            }

        }

        return null;
    }
}
