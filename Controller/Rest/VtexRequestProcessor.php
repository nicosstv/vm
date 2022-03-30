<?php

declare(strict_types=1);

namespace Vtex\VtexMagento\Controller\Rest;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Webapi\Controller\Rest\RequestProcessorInterface;

/**
 * custom REST request processor for synchronous requests
 */
class VtexRequestProcessor implements RequestProcessorInterface
{
    const PROCESSOR_PATH = "/^\\/fulfillment\\//";

    /**
     * @var RestResponse
     */
    private $response;

    /**
     * @var InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var FieldsFilter
     */
    private $fieldsFilter;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected $tokenFactory;

    /**
     * Initial dependencies
     *
     * @param \Magento\Framework\Webapi\Rest\Response $response
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor
     * @param \Magento\Framework\Webapi\Rest\Response\FieldsFilter $fieldsFilter
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        RestResponse $response,
        InputParamsResolver $inputParamsResolver,
        ServiceOutputProcessor $serviceOutputProcessor,
        FieldsFilter $fieldsFilter,
        DeploymentConfig $deploymentConfig,
        ObjectManagerInterface $objectManager,
        TokenFactory $tokenFactory
    ) {
        $this->response = $response;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->fieldsFilter = $fieldsFilter;
        $this->deploymentConfig = $deploymentConfig;
        $this->objectManager = $objectManager;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     *  {@inheritdoc}
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        if (!$this->auth($request)) {
            return false;
        }

        $inputParams = $this->inputParamsResolver->resolve();

        $route = $this->inputParamsResolver->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $service = $this->objectManager->get($serviceClassName);

        /**
         * @var \Magento\Framework\Api\AbstractExtensibleObject $outputData
         */
        $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
        $outputData = $this->serviceOutputProcessor->process(
            $outputData,
            $serviceClassName,
            $serviceMethodName
        );
        if ($request->getParam(FieldsFilter::FILTER_PARAMETER) && is_array($outputData)) {
            $outputData = $this->fieldsFilter->filter($outputData);
        }
        $header = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT);
        if ($header) {
            $this->response->setHeader('X-Frame-Options', $header);
        }
        $this->response->prepareResponse($outputData);
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request)
    {
        if (preg_match(self::PROCESSOR_PATH, $request->getPathInfo()) === 1) {
            return true;
        }
        return false;
    }

    public function auth($request)
    {
        if (strstr($request->getPathInfo(), '/fulfillment/mgt/invoices')) {
            return true;
        }

        $bearerToken = $request->getParam('token');

        if (!$bearerToken) {
            return false;
        }

        $token = $this->tokenFactory->create()->loadByToken($bearerToken);
        if (!$token->getId() || $token->getRevoked()) {
            return false;
        }

        return true;
    }
}
