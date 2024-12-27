<?php
declare(strict_types=1);

namespace Magelearn\Story\Block\View;

use Magelearn\Story\Model\ConfigProvider;
use Magelearn\Story\Model\ImageProcessor;
use Magelearn\Story\Model\Story as storyModel;
use Magelearn\Story\Model\ResourceModel\Gallery\Collection;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Story front block.
 */
class Story extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ConfigProvider
     */
    public $configProvider;

    /**
     * @var storyModel
     */
    private $storyModel;

    /**
     * @var \Magelearn\Story\Helper\Data
     */
    public $dataHelper;

    /**
     * @var Collection
     */
    private $galleryCollection;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var RegionFactory
     */
    private $regionFactory;
    
    /**
     * @var FilterProvider
     */
    private $filterProvider;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        ConfigProvider $configProvider,
        storyModel $storyModel,
        Collection $galleryCollection,
        ImageProcessor $imageProcessor,
        RegionFactory $regionFactory,
        \Magelearn\Story\Helper\Data $dataHelper,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->configProvider = $configProvider;
        $this->storyModel = $storyModel;
        $this->galleryCollection = $galleryCollection;
        $this->imageProcessor = $imageProcessor;
        $this->regionFactory = $regionFactory;
        $this->dataHelper = $dataHelper;
        $this->filterProvider = $filterProvider;
    }

    public function getCacheLifetime()
    {
        return null;
    }
    
    /**
     * @return storyModel|bool
     */
    public function getCurrentStory()
    {
        if ($this->getStoryId()) {
            try {
                $this->storyModel->load($this->getStoryId());

                return $this->storyModel;
                //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Exception $e) {
            }
        }

        return false;
    }
    
    /**
     * Get full description of story
     *
     * @return string
     */
    public function getStoryDescription($story)
    {
        $description = '';
        if ($story->getDescription()) {
            $description = $story->getDescription();
        }
        return $this->filterProvider->getPageFilter()->filter($description);
    }

    /**
     * @return array
     */
    public function getStoryGallery()
    {
        $storyId = $this->getStoryId();
        $storyImages = $this->galleryCollection->getImagesByStory($storyId);
        $result = [];

        foreach ($storyImages as $image) {
            array_push(
                $result,
                [
                    'name'    => $image->getData('image_name'),
                    'is_base' => (bool)$image->getData('is_base'),
                    'path'    => $this->imageProcessor->getImageUrl(
                        [ImageProcessor::ML_STORY_GALLERIES_MEDIA_PATH, $storyId, $image->getData('image_name')]
                    )
                ]
            );
        }

        return $result;
    }
    
    public function getStoryPhoto($imagePath)
    {
        $storyId = $this->getStoryId();
        return $this->imageProcessor->getImageUrl(
            [ImageProcessor::ML_STORY_MEDIA_PATH, $storyId, $imagePath]
            );
    }

    /**
     * @return int
     */
    public function getStoryId()
    {
        if (!$this->hasData('story_id')) {
            $this->setData('story_id', $this->coreRegistry->registry('mlstory_current_story_id'));
        }

        return (int)$this->getData('story_id');
    }

    /**
     * Add metadata to page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $story = $this->getCurrentStory();
        if ($story) {
            if ($description = $story->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($story->getMetaTitle());
            }
            /** @var \Magento\Theme\Block\Html\Title $headingBlock */
            if ($headingBlock = $this->getLayout()->getBlock('page.main.title')) {
                $headingBlock->setPageTitle($story->getName());
            }
            if ($description = $story->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }
            if ($metaRobots = $story->getMetaRobots()) {
                $this->pageConfig->setRobots($metaRobots);
            }
            if ($canonical = $story->getCanonicalUrl()) {
                $this->pageConfig->addRemotePageAsset(
                    $canonical,
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }
        }

        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');

        if ($story && $breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'mlstory',
                [
                    'label' => $this->configProvider->getLabel(),
                    'title' => $this->configProvider->getLabel(),
                    'link' => $this->_urlBuilder->getUrl($this->configProvider->getUrl())
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'story_page',
                [
                    'label' => $story->getName(),
                    'title' => $story->getName()
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [storyModel::CACHE_TAG . '_' . $this->getStoryId()];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + ['l_id' => $this->getStoryId()];
    }
}
