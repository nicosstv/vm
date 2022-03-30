<?php

declare(strict_types=1);

namespace Vtex\VtexMagento\Api;

use Magento\Catalog\Model\Product\Interceptor as Product;

class ApiVtex extends Vtex
{
    protected $api_endpoint = "https://api.vtex.com/{{vendor}}/";

    public function sendSKUSuggestion(Product $product)
    {
        $data = $product->getData();
        $body = $this->productHelper->getProductData($product, $this->sellerId);

        if (count($body['Images'])) {
            try {
                $this->client->put("suggestions/{$this->sellerId}/{$data['entity_id']}", [
                    'json' => $body
                ]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function setPriceV2(Product $product, $vtexData)
    {
        $data = $product->getData();

        try {
            $this->client->put("pricing/prices/{$vtexData['skus'][0]['id']}", [
                'json' => [
                    'costPrice' => (float)$data['price'],
                    'basePrice' => (float)$data['price']
                ]
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
