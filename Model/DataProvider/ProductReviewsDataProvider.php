<?php
/**
 * ScandiPWA - Progressive Web App for Magento
 *
 * Copyright © Scandiweb, Inc. All rights reserved.
 * See LICENSE for license details.
 *
 * @license OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * @package scandipwa/review-graphql
 * @link    https://github.com/scandipwa/review-graphql
 */

declare(strict_types=1);

namespace ScandiPWA\ReviewGraphQl\Model\DataProvider;

use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\ReviewGraphQl\Model\DataProvider\ProductReviewsDataProvider as SourceProductReviewsDataProvider;

/**
 * Class ProductReviewsDataProvider
 * @package ScandiPWA\ReviewGraphQl\Model\DataProvider
 */
class ProductReviewsDataProvider extends SourceProductReviewsDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($collectionFactory);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get product reviews
     * Unlike the original getData function, getReviewsData adds a filter by store
     *
     * @param int $productId
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return Collection
     */
    public function getReviewsData(int $productId, int $currentPage, int $pageSize, int $storeID = null): Collection
    {
        /** @var Collection $reviewsCollection */
        $reviewsCollection = $this->collectionFactory->create()
            ->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $productId)
            ->addStoreFilter($storeID)
            ->setPageSize($pageSize)
            ->setCurPage($currentPage)
            ->setDateOrder();
        $reviewsCollection->getSelect()->join(
            ['cpe' => $reviewsCollection->getTable('catalog_product_entity')],
            'cpe.entity_id = main_table.entity_pk_value',
            ['sku']
        );
        $reviewsCollection->addRateVotes();

        return $reviewsCollection;
    }
}
