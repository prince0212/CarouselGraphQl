<?php

namespace Deloitte\CarouselGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepositoryInterfaceAlias;
use Magento\Framework\Api\SearchCriteriaBuilder as SearchCriteriaBuilderAlias;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product collection resolver
 */
class ProductsResolver implements ResolverInterface
{
    private StoreManagerInterface $storemanager;

    /**
     * @param ProductRepositoryInterfaceAlias $productRepository
     * @param SearchCriteriaBuilderAlias $searchCriteriaBuilder
     * @param StoreManagerInterface $storemanager
     */
    public function __construct(
        ProductRepositoryInterfaceAlias $productRepository,
        SearchCriteriaBuilderAlias $searchCriteriaBuilder,
        StoreManagerInterface $storemanager
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_storeManager = $storemanager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $productsData = $this->getProductsData();
        return $productsData;
    }

    private function getProductsData()
    {
        try {
            /* filter for all the pages */
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('entity_id', 1,'gteq')
                ->addFilter('visibility', value: 4)
                ->setPageSize(10)
                ->create();
            $products = $this->productRepository->getList($searchCriteria)->getItems();
            $productRecord['allProducts'] = [];
            $store = $this->_storeManager->getStore();
            foreach($products as $product) {
                $productRecord['allProducts'][$product->getId()]['sku'] = $product->getSku();
                $productRecord['allProducts'][$product->getId()]['name'] = $product->getName();
                $productRecord['allProducts'][$product->getId()]['price'] = $product->getPrice();
                $productRecord['allProducts'][$product->getId()]['url_key'] = $product->getUrlKey();
                $productRecord['allProducts'][$product->getId()]['mediaUrl'] = $product->getData('small_image');
                $productRecord['allProducts'][$product->getId()]['currency_code'] = $store->getCurrentCurrencyCode();
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productRecord;
    }


}
