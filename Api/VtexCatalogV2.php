<?php

declare(strict_types=1);

namespace Vtex\VtexMagento\Api;

class VtexCatalogV2 extends Vtex
{
    protected $api_endpoint = "https://portal.vtexcommercestable.com.br/api/catalogv2/";

    public function getCategoryById($id)
    {
        try {
            $response = $this->client->get("category-tree/categories/{$id}?an={$this->settings->getVendorName()}&depth=0");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    public function createCategory($data)
    {
        try {
            $this->client->post("category-tree/categories?an={$this->settings->getVendorName()}", [
                'json' => [
                    "parentId" => ($data['parent_id'] == 1) ? null : $data['parent_id'],
                    "Name" => $data['name']
                ]
            ]);
            return null;
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return $e->getMessage();
        }
    }

    public function getBrand($data)
    {
        try {
            $response = $this->client->get("brands?an={$this->settings->getVendorName()}&name={$data['label']}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    public function createBrand($data)
    {
        try {
            $this->client->post("brands?an={$this->settings->getVendorName()}", [
                'json' => [
                    "name" => $data['label'],
                    "metaTagDescription" => $data['label'],
                    "keywords" => [],
                    "siteTitle" => $data['label'],
                    "isActive" => true,
                    "slug" => '',
                    "score" => 0,
                    "displayOnMenu" => true
                ]
            ]);
            return null;
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return $e->getMessage();
        }
    }

    public function getProduct($data)
    {
        try {
            $response = $this->client->get("products?an={$this->settings->getVendorName()}&externalid={$this->settings->getSellerId()}-{$data->getData('entity_id')}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    public function createProduct($data)
    {
        $brand = $data->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($data);
        $brandId = null;
        if ($brand) {
            $brandId = ($brand = $this->getBrand(['label' => $brand])) ? $brand['id'] : null;
        }

        $body = $this->productHelper->getProductDataV2($data, $brandId);
        if (count($body['images'])) {
            try {
                $response = $this->client->post("products?an={$this->settings->getVendorName()}", [
                    'json' => $body
                ]);
                return json_decode($response->getBody()->getContents(), true);
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
                return false;
            }
        }
    }

    public function updateProduct($originalData, $product)
    {
        $brand = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
        $brandId = null;
        if ($brand) {
            $brandId = ($brand = $this->getBrand(['label' => $brand])) ? $brand['id'] : null;
        }

        $body = $this->productHelper->getProductDataV2($product, $brandId);
        $body['id'] = $originalData['id'];
        foreach ($body['skus'] as $k => $sku) {
            $key = array_search($sku['externalId'], array_column($originalData['skus'], 'externalId'));
            if ($key !== false) {
                $body['skus'][$k]['id'] = $originalData['skus'][$key]['id'];
            }
        }
//        $body['categoryNames'] = $originalData['categoryNames'];
//        $body['mainCategoryBranch'] = $originalData['mainCategoryBranch'];
        try {
            $response = $this->client->put("products/{$originalData['id']}?an={$this->settings->getVendorName()}", [
                'json' => $body
            ]);
            if ($response->getStatusCode() == 204) {
                return true;
            }
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    public function getAllCategories()
    {
        try {
            $response = $this->client->get("category-tree/?an={$this->settings->getVendorName()}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    public function recursiveCategorySearch($needle, $haystack)
    {
        foreach($haystack as $key => $value) {
            if ($value['value']['name'] == $needle) {
                return $value['value']['id'];
            }

            if (is_array($value['children']) && $value['children']) {
                $nextKey = $this->recursiveCategorySearch($needle, $value['children']);
                if ($nextKey) {
                    return $nextKey;
                }
            }
        }
        return null;
    }
}
