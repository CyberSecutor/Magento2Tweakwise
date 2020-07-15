<?php

/**
 * @author : Edwin Jacobs, email: ejacobs@emico.nl.
 * @copyright : Copyright Emico B.V. 2020.
 */

namespace Emico\Tweakwise\Model\Client\Type\SuggestionType;

use Emico\Tweakwise\Model\Catalog\Layer\Url\Strategy\QueryParameterStrategy;
use Emico\TweakwiseExport\Model\Helper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class SuggestionTypeCategory extends SuggestionTypeAbstract
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlInstance;

    /**
     * SuggestionTypeCategory constructor.
     * @param UrlInterface $urlInstance
     * @param CategoryRepository $categoryRepository Empty category model used to resolve urls
     * @param StoreManagerInterface $storeManager
     * @param Helper $exportHelper
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlInstance,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        Helper $exportHelper,
        array $data = []
    ) {
        parent::__construct($exportHelper, $data);
        $this->urlInstance = $urlInstance;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        try {
            $categoryUrl = $this->getCategoryUrl();
            if (!$categoryUrl) {
                return '';
            }

            $categoryIds = $this->getCategoryIds();
            return $this->urlInstance->getDirectUrl(
                $categoryUrl,
                [
                    '_query' => [
                        QueryParameterStrategy::PARAM_CATEGORY => implode('-', $categoryIds)
                    ]
                ]
            );
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getCategoryUrl()
    {
        $categoryIds = $this->getCategoryIds();
        if (empty($categoryIds)) {
            return '';
        }

        $categoryId = end($categoryIds);

        /** @var Category $category */

        if ($categoryId === $this->getStoreRootCategory()) {
            return '';
        }
        $category = $this->categoryRepository->get($categoryId);
        return $category->getUrl();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getStoreRootCategory()
    {
        /** @var Store|StoreInterface $store */
        $store = $this->storeManager->getStore();
        return (int)$store->getRootCategoryId();
    }
}
