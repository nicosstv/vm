<?php
namespace Vtex\VtexMagento\Console;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vtex\VtexMagento\Api\Vtex;
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
        Import $import,
        ImportCollectionFactory $importCollectionFactory,
        SettingsCollectionFactory $settingsCollectionFactory
    )
    {
        $this->vClient = $vClient;
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
        $this->setName('vtex:payments');
        $this->setDescription('Import payment methods into VTEX marketplace');

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
        $objectManager = ObjectManager::getInstance();
        $payment_methods = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Payment\Collection')
            ->create();

        //trigger import start
        $progress = 0;
        $errors = 0;

        $this->import
            ->setData([
                'type' => 'Payment methods import',
                'status' => 'In progress',
                'total' => count($payment_methods),
                'progress' => $progress,
                'errors' => $errors,
                'date' => date('Y-m-d H:i:s')
            ])->save();

        $logFileName = "payment-methods-import-{$this->import->getId()}.txt";
        $logger = $this->getLogger($logFileName);

        $i = 1;
        foreach ($payment_methods as $method) {
        }
    }
}
