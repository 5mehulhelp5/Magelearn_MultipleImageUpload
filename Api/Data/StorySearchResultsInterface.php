<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magelearn\Story\Api\Data;

interface StorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get question list.
     * @return \Magelearn\Story\Api\Data\StoryInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Magelearn\Story\Api\Data\StoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
