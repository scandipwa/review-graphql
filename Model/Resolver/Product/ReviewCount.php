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

namespace ScandiPWA\ReviewGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Model\Resolver\Product\ReviewCount as SourceReviewCount;

/**
 * Class ReviewCount
 * @package ScandiPWA\ReviewGraphQl\Model\Resolver\Product
 */
class ReviewCount extends SourceReviewCount
{
    /**
     * @var Review
     */
    protected Review $review;

    /**
     * @var ReviewsConfig
     */
    protected ReviewsConfig $reviewsConfig;

    /**
     * @param Review $review
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(
        Review $review,
        ReviewsConfig $reviewsConfig)
    {
        $this->review = $review;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolves the product total reviews
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return int|Value|mixed
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $this->reviewsConfig->isEnabled()) {
            return 0;
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $storeID = (int)$context->getExtensionAttributes()->getStore()->getId();

        return (int) $this->review->getTotalReviews($product->getId(), true, $storeID);
    }
}
