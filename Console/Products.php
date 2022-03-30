<?php
namespace Vtex\VtexMagento\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vtex\VtexMagento\Api\ApiVtex;
use Vtex\VtexMagento\Api\Vtex;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Vtex\VtexMagento\Api\VtexCatalogV2;
use Vtex\VtexMagento\Model\Import;
use Vtex\VtexMagento\Model\ResourceModel\Import\CollectionFactory as ImportCollectionFactory;
use Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class Products extends Command
{
    protected $vClient;
    protected $apiVClient;
    protected $catalogV2;
    protected $_productCollection;
    protected $import;
    protected $_importCollectionFactory = null;
    protected $_settingsCollectionFactory = null;
    protected $settings;

    public function __construct(
        Vtex $vClient,
        ApiVtex $apiVClient,
        VtexCatalogV2 $catalogV2,
        ProductCollectionFactory $productCollection,
        Import $import,
        ImportCollectionFactory $importCollectionFactory,
        SettingsCollectionFactory $settingsCollectionFactory
    )
    {
        $this->vClient = $vClient;
        $this->apiVClient = $apiVClient;
        $this->catalogV2 = $catalogV2;
        $this->_productCollection = $productCollection;
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
        $this->setName('vtex:products');
        $this->setDescription('Import products into VTEX marketplace');

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
            ->addFieldToFilter('type', array('like' => 'Products import'))
            ->addFieldToFilter('status', array('like' => 'In progress'))
            ->load();

        if (!$importCollection->count()) {

            $productCollection = $this->_productCollection->create()
                ->addAttributeToSelect('*');

            //trigger import start
            $progress = 0;
            $errors = 0;

            $this->import
                ->setData([
                    'type' => 'Products import',
                    'status' => 'In progress',
                    'total' => count($productCollection),
                    'progress' => $progress,
                    'errors' => $errors,
                    'date' => date('Y-m-d H:i:s')
                ])->save();

            $logFileName = "product-import-{$this->import->getId()}.txt";
            $logger = $this->getLogger($logFileName);

            $this->import
                ->addData([
                    'filename' => $logFileName
                ])->save();

            $i = 1;
            foreach ($productCollection as $product) {

                try {

                    $response = null;

                    if ($this->settings->getCatalogV2()) {
                        if (!$existingProduct = $this->catalogV2->getProduct($product)) {
                            if (!$existingProduct = $this->catalogV2->createProduct($product)) {
                                $this->apiVClient->setPriceV2($product, $existingProduct);
                                $this->vClient->setInventoryV2($product, $existingProduct);
                                $response = "Line {$i}: Product `{$product->getName()}` successfully imported!";
                            }
                        }
//                    else {
//                        if ($this->catalogV2->updateProduct($existingProduct, $product)) {
//                            $this->apiVClient->setPriceV2($product, $existingProduct);
//                            $this->vClient->setInventoryV2($product, $existingProduct);
//                            $response = "Line {$i}: Product `{$product->getName()}` already exists in VTEX!";
//                        } else {
//                            $response = "Product `{$product->getName()}` unable to update!";
//                            $errors++;
//                        }
//                    }
                    } else {
                        try {
                            $this->vClient->changeNotification($product->getData('entity_id'));
                            $response = "Product `{$product->getName()}` already exists in VTEX!";
                            $errors++;
                        } catch (\Exception $e) {
                            $this->vClient->sendSKUSpecifications($product);
                            $this->apiVClient->sendSKUSuggestion($product);
                            $response = "Product `{$product->getName()}` successfully imported!";
                        }
                    }


                    $progress++;
                    $this->import
                        ->addData(['progress' => $progress, 'errors' => $errors])
                        ->save();

                    $i++;
                    if ($response) {
                        try {
                            $logger->info($response);
                        } catch (\Exception $e) {
                        }
                    }

                } catch (\Exception $e) {}
            }

            $this->import
                ->addData([
                    'status' => 'Finished'
                ])
                ->save();
        }

    }
}
