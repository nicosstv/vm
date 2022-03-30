<?php
namespace Vtex\VtexMagento\Console;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vtex\VtexMagento\Api\Vtex;
use Vtex\VtexMagento\Api\VtexCatalogV2;
use \Vtex\VtexMagento\Model\Import;
use \Vtex\VtexMagento\Model\ResourceModel\Import\CollectionFactory as ImportCollectionFactory;
use Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;

class Brands extends Command
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
        $this->setName('vtex:brands');
        $this->setDescription('Import brands into VTEX marketplace');

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
            ->addFieldToFilter('type', array('like' => 'Brands import'))
            ->addFieldToFilter('status', array('like' => 'In progress'))
            ->load();

        if (!$importCollection->count()) {

            $objectManager = ObjectManager::getInstance();
            $attribute = $objectManager->get(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class)
                ->get('manufacturer');

            $brands = $attribute->getOptions();

            //trigger import start
            $progress = 0;
            $errors = 0;

            $this->import
                ->setData([
                    'type' => 'Brands import',
                    'status' => 'In progress',
                    'total' => count($brands),
                    'progress' => $progress,
                    'errors' => $errors,
                    'date' => date('Y-m-d H:i:s')
                ])->save();

            $logFileName = "brand-import-{$this->import->getId()}.txt";
            $logger = $this->getLogger($logFileName);

            $i = 1;
            foreach ($brands as $option) {

                if ($option->getValue() != '') {

                    if ($this->settings->getCatalogV2()) {
                        if (!$this->catalogV2->getBrand($option->getData())) {
                            if (!$response = $this->catalogV2->createBrand($option->getData())) {
                                $response = "Line {$i}: Brand `{$option->getLabel()}` successfully imported!";
                            }
                        } else {
                            $response = "Line {$i}: Brand `{$option->getLabel()}` already exists in VTEX!";
                        }
                    } else {
                        if (!$this->vClient->getBrand($option->getData())) {
                            if (!$response = $this->vClient->createBrand($option->getData())) {
                                $response = "Line {$i}: Brand `{$option->getLabel()}` successfully imported!";
                            }
                        } else {
                            $response = "Line {$i}: Brand `{$option->getLabel()}` already exists in VTEX!";
                            $errors++;
                        }
                    }

                } else {
                    $response = "Line {$i}: Invalid brand name `{$option->getLabel()}`";
                    $errors++;
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
}
