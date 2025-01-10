<?php
declare(strict_types=1);

namespace Magelearn\Story\Model;

use Magelearn\Story\Api\Data\StoryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;

class Story extends AbstractModel implements StoryInterface
{
    public const CACHE_TAG = 'mlstory_story';
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;
    
    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    
    /**
     * @var ConfigHtmlConverter
     */
    private $configHtmlConverter;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        ImageProcessor $imageProcessor,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        ConfigHtmlConverter $configHtmlConverter,
        \Magelearn\Story\Model\ResourceModel\Story $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->imageProcessor = $imageProcessor;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
            );
        $this->filterProvider = $filterProvider;
        $this->configHtmlConverter = $configHtmlConverter;
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magelearn\Story\Model\ResourceModel\Story::class);
    }
    
    /**
     * Get story associated store Ids
     * Note: Story can be for All Store View (sore_ids = array(0 => '0'))
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreIds()
    {
        $storesArray = explode(',', $this->_getData('stores'));
        
        return array_filter($storesArray);
    }

    /**
     * Get story associated website Ids
     * Note: Story can be for All Store View (sore_ids = array(0))
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $stores = $this->getStoreIds();
            $websiteIds = [];
            foreach ($stores as $storeId) {
                $websiteIds[] = $this->storeManager->getStore($storeId)->getWebsiteId();
            }
            $this->setData('website_ids', array_unique($websiteIds));
        }
        
        return $this->_getData('website_ids');
    }
    /**
     * Set templates html
     */
    public function setTemplatesHtml()
    {
        $this->configHtmlConverter->setHtml($this);
    }

    /**
     * @return string
     */
    public function getPhotoMediaUrl()
    {
        if ($this->getPhotoImg()) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::ML_STORY_MEDIA_PATH, $this->getId(), $this->getPhotoImg()]
                );
        }
    }

    /**
     * Optimized get data method
     *
     * @return array
     */
    public function getFrontendData(): array
    {
        $result = [
            'id' => (int)$this->getDataByKey('id')
        ];
        
        if ($this->getDataByKey('photo_url')) {
            $result['photo_url'] = $this->getDataByKey('photo_url');
        }
        
        return $result;
    }
    
    /**
     * Generate SEO-friendly URL for the story
     *
     * @param bool $canonical Whether to return canonical URL
     * @return string
     */
    public function getStoryUrl($canonical = false)
    {
        // If canonical URL is set and not empty, return it
        if ($canonical && $this->getCanonicalUrl()) {
            return $this->getCanonicalUrl();
        }
        
        // Generate SEO-friendly URL using url_key
        $urlKey = $this->getUrlKey();
        
        // Fallback to ID if no URL key exists
        if (!$urlKey) {
            $urlKey = 'story-' . $this->getId();
        }
        
        // Ensure URL key is URL-friendly
        $urlKey = $this->sanitizeUrlKey($urlKey);
        
        return 'story/view/' . $urlKey;
    }
    
    /**
     * Sanitize URL key to make it URL-friendly
     *
     * @param string $urlKey
     * @return string
     */
    private function sanitizeUrlKey($urlKey)
    {
        // Convert to lowercase
        $urlKey = strtolower($urlKey);
        
        // Replace non-alphanumeric characters with hyphens
        $urlKey = preg_replace('/[^a-z0-9-]/', '-', $urlKey);
        
        // Remove multiple consecutive hyphens
        $urlKey = preg_replace('/-+/', '-', $urlKey);
        
        // Trim hyphens from beginning and end
        $urlKey = trim($urlKey, '-');
        
        return $urlKey;
    }

    public function activate()
    {
        $this->setStatus(1);
        $this->setData('massAction', true);
        $this->save();
        
        return $this;
    }
    
    public function inactivate()
    {
        $this->setStatus(0);
        $this->setData('massAction', true);
        $this->save();
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return Int|null
     */
    public function getStatus(): ?Int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param Int|null $status
     */
    public function setStatus(?Int $status): void
    {
        $this->setData(self::STATUS, $status);
    }
    
    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->getData(self::POSITION);
    }
    
    /**
     * @param string|null $position
     */
    public function setPosition(?string $position): void
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->getData(self::PHOTO);
    }

    /**
     * @param string|null $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->setData(self::PHOTO, $photo);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }
    
    /**
     * @return string|null
     */
    public function getStores(): ?string
    {
        return $this->getData(self::STORES);
    }
    
    /**
     * @param string|null $stores
     */
    public function setStores(?string $stores): void
    {
        $this->setData(self::STORES, $stores);
    }
    
    /**
     * @return string|null
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }
    
    /**
     * @param string|null $urlKey
     */
    public function setUrlKey(?string $urlKey): void
    {
        $this->setData(self::URL_KEY, $urlKey);
    }
    
    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->getData(self::META_TITLE);
    }
    
    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->setData(self::META_TITLE, $metaTitle);
    }
    
    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->getData(self::META_DESCRIPTION);
    }
    
    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->setData(self::META_DESCRIPTION, $metaDescription);
    }
    
    /**
     * @return string|null
     */
    public function getMetaRobots(): ?string
    {
        return $this->getData(self::META_ROBOTS);
    }
    
    /**
     * @param string|null $metaRobots
     */
    public function setMetaRobots(?string $metaRobots): void
    {
        $this->setData(self::META_ROBOTS, $metaRobots);
    }
    
    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->getData(self::CANONICAL_URL);
    }
    
    /**
     * @param string|null $canonicalUrl
     */
    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string|null $createdAt
     */
    public function setCreatedAt(?string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string|null $updatedAt
     */
    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

