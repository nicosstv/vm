<?php

namespace Vtex\VtexMagento\Block;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Backend\Block\Template\Context as TemplateContext;
use \Vtex\VtexMagento\Model\ResourceModel\Settings\Collection as SettingsCollection;
use \Vtex\VtexMagento\Model\ResourceModel\Settings\CollectionFactory as SettingsCollectionFactory;
use \Vtex\VtexMagento\Model\Settings;
use \Vtex\VtexMagento\Model\ResourceModel\Import\CollectionFactory as ImportCollectionFactory;
use \Vtex\VtexMagento\Model\ResourceModel\Mapping\CollectionFactory as MappingCollectionFactory;
use \Vtex\VtexMagento\Model\ResourceModel\Mapping\PaymentMethodsCollectionFactory as MappingPaymentMethodsCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Payment\Helper\Data as paymentData;

class Setting extends Template
{
    protected $_settingsCollectionFactory = null;
    protected $_importsCollectionFactory = null;
    protected $_mappingCollectionFactory = null;
    protected $_mappingPaymentMethodsCollectionFactory = null;
    protected $request;
    protected $formKey;
    protected $_backendUrl;
    protected $logger;
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param TemplateContext $template_context
     * @param SettingsCollectionFactory $settingsCollectionFactory
     * @param ImportCollectionFactory $importsCollectionFactory
     * @param MappingCollectionFactory $mappingCollectionFactory
     * @param MappingPaymentMethodsCollectionFactory $mappingPaymentMethodsCollectionFactory
     * @param UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        paymentData $paymentHelper,
        TemplateContext $template_context,
        SettingsCollectionFactory $settingsCollectionFactory,
        ImportCollectionFactory $importsCollectionFactory,
        MappingCollectionFactory $mappingCollectionFactory,
        MappingPaymentMethodsCollectionFactory $mappingPaymentMethodsCollectionFactory,
        UrlInterface $backendUrl,
        array $data = []
    )
    {
        $this->logger = $logger;
        $this->paymentHelper = $paymentHelper;
        $this->_backendUrl = $backendUrl;
        $this->_settingsCollectionFactory = $settingsCollectionFactory;
        $this->_importsCollectionFactory = $importsCollectionFactory;
        $this->_mappingCollectionFactory = $mappingCollectionFactory;
        $this->_mappingPaymentMethodsCollectionFactory = $mappingPaymentMethodsCollectionFactory;
        $this->formKey = $template_context->getFormKey();
        parent::__construct($context, $data);
    }

    /**
     * @return Settings[]
     */
    public function getSettings()
    {
        $settings = [
            'settings_id' => '',
            'app_key' => '',
            'app_token' => '',
            'vendor_name' => '',
            'seller_id' => '',
            'vtex_aut_cookie' => '',
            'salesChannel' => '',
            'catalog_v2' => '',
        ];
        /** @var SettingsCollection $settingsCollection */
        $settingsCollection = $this->_settingsCollectionFactory->create();
        $settingsCollection->addFieldToSelect('*')->load();

        if ($settingsCollection->getItems()) {
            $settingsC = $settingsCollection->getFirstItem();
            $settings['settings_id'] = $settingsC->getSettingsId();
            $settings['app_key'] = $settingsC->getAppKey();
            $settings['app_token'] = $settingsC->getAppToken();
            $settings['vendor_name'] = $settingsC->getVendorName();
            $settings['seller_id'] = $settingsC->getSellerId();
            $settings['vtex_aut_cookie'] = $settingsC->getVtexAutCookie();
            $settings['salesChannel'] = $settingsC->getSalesChannel() ? $settingsC->getSalesChannel() : '1';
            $settings['catalog_v2'] = $settingsC->getCatalogV2();
        }

        return $settings;
    }

    public function getImportLogs()
    {
        return $this->_importsCollectionFactory->create()->setOrder('date', 'desc');
    }

    public function getMappings()
    {
        return $this->_mappingCollectionFactory->create()->setOrder('id', 'asc');
    }

    public function getPaymentMethodsMappings() {
        return $this->_mappingPaymentMethodsCollectionFactory->create()->setOrder('id', 'asc');
    }

    public function getPostUrl()
    {
        return $this->_backendUrl->getUrl("vtexmagento/settings/save", []);
    }

    public function getSavePaymentMethodsMappingsUrl() {
        return $this->_backendUrl->getUrl("vtexmagento/settings/savePaymentMethodsMappings", []);
    }

    public function getSaveMappingsUrl()
    {
        return $this->_backendUrl->getUrl("vtexmagento/settings/saveMappings", []);
    }

    public function getPaymentMethods() {
        return array_filter($this->paymentHelper->getPaymentMethods(), function($item, $key) {
            return isset($item['active']) && $item['active'] && isset($item['group']) &&
            $item['group'] == 'offline' && $key != 'free';
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getCategories()
    {
        $objectManager = ObjectManager::getInstance();
        $categories = $objectManager
            ->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')
            ->create();
        $categories->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1');
        $data = $categories->getData();

        foreach ($data as $k => $category) {
            $model = $objectManager->create('Magento\Catalog\Model\Category')->load($category['entity_id']);
            $data[$k]['name'] = $model->getName();
        }
        return $data;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function createTreeViewHTML()
    {
        $array = $this->createTreeViewArray($this->getMappings()->setPageSize(10)->getData());

        //get all magento categories
        $objectManager = ObjectManager::getInstance();
        $categories = $objectManager
            ->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')
            ->create();
        $categories->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1');
        $categories = $categories->getData();

        usort($categories, function($a, $b) {
            return $a['entity_id'] - $b['entity_id'];
        });
        return $this->nested2ul($array, $categories);
    }

    public function createTreeViewArray($array)
    {
        $new = array();

        foreach ($array as $a){
            if ($a['parent_id']) {
                $new[$a['parent_id']][] = $a;
            }
        }

        if (isset($array[0])) {
            return $this->createTree($new, array($array[0]));
        }

        return [];
    }

    protected function nested2ul($data, $categories)
    {
        $result = array();

        if (!empty($data) && $data[0] != null) {
            $result[] = '<tr class="data-row">';
            foreach ($data as $entry) {
                $children = isset($entry['children']) ? $entry['children'] : [];
                $result[] = sprintf(
                    '<td class="">%s <select name="mapping[%s]" class="admin__control-select">%s</select> %s</td>', $entry['vtex_name'], $entry['id'], $this->options($categories, $entry['magento_id']), $this->nested2ul($children, $categories)
                );
            }
            $result[] = '</tr>';
        }

        return implode($result);
    }

    public function paymentOptions($payment_methods, $magento_name = null) {
        $result = array();
        $result[] = '<option value=""></option>';
        foreach ($payment_methods as $key => $method) {
            if(isset($method['title'])) {
                $result[] = sprintf(
                    '<option value="%s{{*}}%s" %s>%s</option>',
                    $key,
                    $method['title'],
                    $magento_name ? ($magento_name == $method['title'] ? 'selected' : null) : null,
                    $method['title']
                );
            }
        }
        return implode($result);
    }

    public function options($categories, $magento_id = null)
    {
        $result = array();
        $result[] = '<option value=""></option>';

        foreach ($categories as $category) {
            $result[] = sprintf(
                '<option value="%s" %s>%s</option>',
                $category['entity_id'],
                $magento_id ? ($magento_id == $category['entity_id'] ? 'selected' : null) : null,
                $category['name']
            );
        }
        return implode($result);
    }

    protected function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k => $l){
            if(isset($list[$l['id']])){
                $l['children'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

}
