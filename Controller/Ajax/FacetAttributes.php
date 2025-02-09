<?php

namespace Tweakwise\Magento2Tweakwise\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Tweakwise\Magento2Tweakwise\Exception\ApiException;
use Tweakwise\Magento2Tweakwise\Model\Client;
use Tweakwise\Magento2Tweakwise\Model\Client\RequestFactory;

/**
 * Class Navigation
 * Handles ajax filtering requests for category pages
 * @package Tweakwise\Magento2Tweakwise\Controller\Ajax
 */
class FacetAttributes extends Action
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var RequestFactory
     */
    private RequestFactory $requestFactory;

    public function __construct(Context $context, JsonFactory $jsonFactory, RequestFactory $requestFactory, Client $client)
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->requestFactory = $requestFactory;
        $this->client = $client;
    }

    public function execute()
    {
        $json = $this->resultFactory->create('json');
        $facetRequest = $this->requestFactory->create();

        $categoryId = $this->getRequest()->getParam('category');
        $facetKey = $this->getRequest()->getParam('facetkey');
        //remove category id for now. It can give the wrong store id for the admin which results in the wrong tncid
        //$facetRequest->addCategoryFilter($categoryId);
        $facetRequest->addFacetKey($facetKey);

        $result = [];
        try {
            $response = $this->client->request($facetRequest);
            foreach ($response->getAttributes() as $attribute) {
                $result[] = ['value' => $attribute['title'], 'label' => $attribute['title']];
            }
        } catch (ApiException $e) {
            if (!$e->getCode() == 404) {
                throw $e;
            }
        }

        $json->setData(['data' => $result]);
        return $json;
    }
}
