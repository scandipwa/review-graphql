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

namespace ScandiPWA\ReviewGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Store\Api\Data\StoreInterface;
use Magento\ReviewGraphQl\Model\Resolver\ProductReviewRatingsMetadata as SourceProductReviewRatingsMetadata;

/**
 * Class ProductReviewRatingsMetadata
 * @package ScandiPWA\ReviewGraphQl\Model\Resolver
 */
class ProductReviewRatingsMetadata extends SourceProductReviewRatingsMetadata
{
    /**
     * @var RatingCollectionFactory
     */
    protected RatingCollectionFactory $ratingCollectionFactory;

    /**
     * @var ReviewsConfig
     */
    protected ReviewsConfig $reviewsConfig;

    /**
     * ProductReviewRatingsMetadata constructor.
     * @param RatingCollectionFactory $ratingCollectionFactory
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(
        RatingCollectionFactory $ratingCollectionFactory,
        ReviewsConfig $reviewsConfig
    ) {
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array[]|Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $this->reviewsConfig->isEnabled()) {
            return ['items' => []];
        }

        $items = [];
        $storeID = $context->getExtensionAttributes()->getStore()->getId();

        $ratingCollection = $this->ratingCollectionFactory->create();
        $ratingCollection->addEntityFilter(Review::ENTITY_PRODUCT_CODE)
            ->addRatingPerStoreName($storeID)
            ->setStoreFilter($storeID)
            ->setActiveFilter(true)
            ->setPositionOrder()
            ->addOptionToItems();

        foreach ($ratingCollection->getItems() as $item) {
            $items[] = [
                'id' => base64_encode($item->getData('rating_id')),
                'name' => $item->getData('rating_code'),
                'values' => $item->getData('options')
            ];
        }

        return ['items' => $items];
    }
}
