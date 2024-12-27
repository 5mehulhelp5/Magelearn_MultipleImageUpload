<?php
declare(strict_types=1);

namespace Magelearn\Story\Model\ResourceModel\Gallery;

use Magelearn\Story\Model\Gallery;
use Magelearn\Story\Model\ResourceModel\Gallery as GalleryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Gallery[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CollectionFactory $factory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->factory = $factory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Gallery::class, GalleryResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getImagesByStory($storyId)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Gallery[] $images */
        $images = $imagesCollection->addFieldToFilter('story_id', $storyId)->getItems();

        return $images;
    }

    public function getByNameAndStory($storyId, $name)
    {
        /** @var Collection $imagesCollection */
        $imagesCollection = $this->factory->create();

        /** @var Gallery $image */
        $image = $imagesCollection
        ->addFieldToFilter('story_id', $storyId)
            ->addFieldToFilter('image_name', $name)
            ->getFirstItem();

        return $image;
    }

    /**
     * @param string $storyId
     *
     * @return \Magelearn\Story\Model\Gallery
     */
    public function getBaseStoryImage($storyId)
    {
        $imagesCollection = $this->factory->create();
        $imagesCollection
        ->addFieldToFilter('story_id', $storyId)
            ->addFieldToFilter('is_base', 1)
            ->addOrder('is_base');

        return $imagesCollection->getFirstItem();
    }
}
