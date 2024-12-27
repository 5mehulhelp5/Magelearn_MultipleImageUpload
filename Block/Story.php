<?php
declare(strict_types=1);

namespace Magelearn\Story\Block;

use Magelearn\Story\Helper\Data;
use Magelearn\Story\Model\BaseImageStory;
use Magelearn\Story\Model\ConfigProvider;
use Magelearn\Story\Model\ImageProcessor;
use Magelearn\Story\Model\Story as StoryModel;
use Magelearn\Story\Model\ResourceModel\Story\Collection as StoryCollection;
use Magelearn\Story\Model\ResourceModel\Story\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Cms\Model\Template\FilterProvider;

class Story extends Template implements IdentityInterface
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Magelearn_Story::center.phtml';
    
    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    
    /**
     * @var File
     */
    protected $ioFile;
    
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    
    /**
     * @var Data
     */
    public $dataHelper;
    
    /**
     * @var ConfigProvider
     */
    public $configProvider;
    
    /**
     * @var StoryCollection
     */
    private $storyCollection;
    
    /**
     * @var bool
     */
    private $isStoryCollectionPrepared = false;
    
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;
    
    /**
     * @var CollectionFactory
     */
    private $storyCollectionFactory;
    
    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    private $pager;
    
    /**
     * @var BaseImageStory
     */
    private $baseImageStory;
    
    /**
     * @var Escaper
     */
    private $escaper;
    
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    
    protected $logger;
    
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        File $ioFile,
        Data $dataHelper,
        ConfigProvider $configProvider,
        ImageProcessor $imageProcessor,
        CollectionFactory $storyCollectionFactory,
        BaseImageStory $baseImageStory,
        FilterProvider $filterProvider,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
        ) {
            $this->coreRegistry = $coreRegistry;
            $this->filesystem = $context->getFilesystem();
            $this->jsonEncoder = $jsonEncoder;
            $this->ioFile = $ioFile;
            parent::__construct($context, $data);
            $this->dataHelper = $dataHelper;
            $this->configProvider = $configProvider;
            $this->storyCollectionFactory = $storyCollectionFactory;
            $this->imageProcessor = $imageProcessor;
            $this->baseImageStory = $baseImageStory;
            $this->filterProvider = $filterProvider;
            $this->escaper = $escaper;
            $this->urlBuilder = $urlBuilder;
            $this->logger = $logger;
    }
    
    /**
     * Return title of SStory
     *
     * @param StoryModel $story
     *
     * @return string
     */
    public function getStoryTitle($story)
    {
        if ($story->getUrlKey() && $this->configProvider->getEnablePages()) {
            return '<a class="mlstory-link" href="' . $this->getStoryUrl($story)
            . '" title="' . $this->escaper->escapeHtml($story->getName())
            . '" target="_blank">'
                . $this->escaper->escapeHtml($story->getName()) . '</a>';
        } else {
            return '<div class="mlstory-title">' . $this->escaper->escapeHtml($story->getName())
            . '</div>';
        }
    }
    
    /**
     * Return main image url
     *
     * @param StoryModel $story
     *
     * @return string
     */
    public function getStoryImage($story)
    {
        return $this->baseImageStory->getPhotoImageUrl($story);
    }
    
    /**
     * Get full description of story
     *
     * @return string
     */
    public function getStoryDescription($story, bool $useFilterProcessor = false)
    {
        $descriptionLimit = $this->configProvider->getDescriptionLimit();
        $description = $story->getDescription() ?? '';
        
        if ($useFilterProcessor) {
            $description = $this->filterProvider->getPageFilter()->filter($description);
        }
        $description = strip_tags(
            preg_replace('#(<style.*?>).*?(</style>)#', '$1$2', $description)
            );
        if (strlen($description) < $descriptionLimit) {
            return '<div class="mlstory-description">' . $description . '</div>';
        }
        
        if ($descriptionLimit) {
            if (preg_match('/^(.{' . ($descriptionLimit) . '}.*?)\b/isu', $description, $matches)) {
                $description = $matches[1] . '...';
            }
            
            if ($this->configProvider->getEnablePages()) {
                $description .= '<a href="' . $this->getStoryUrl($story) . '" title="read more" target="_blank"> '
                    . __('Read More') . '</a>';
            }
        }
        
        return '<div class="mlstory-description">' . $description . '</div>';
    }
    
    /**
     * Get Story url
     *
     * @return string
     */
    private function getStoryUrl($story)
    {
        return $this->escaper->escapeUrl(
            $this->urlBuilder->getUrl('story/view/' . $story->getUrlKey())
        );
    }
    
    /**
     * @return StoryCollection
     */
    public function getStoryCollection()
    {
        if (!$this->isStoryCollectionPrepared) {
            $this->getClearStoryCollection();
            $this->storyCollection->joinMainImage();
            
            foreach ($this->storyCollection as $story) {
                $story->setTemplatesHtml();
            }
            $this->isStoryCollectionPrepared = true;
        }

        return $this->storyCollection;
    }
    
    public function getClearStoryCollection(): StoryCollection
    {
        if (!$this->storyCollection) {
            $this->storyCollection = $this->storyCollectionFactory->create();
            $this->storyCollection->applyDefaultFilters();
            //$this->storyCollection->getStoryData();
            $this->storyCollection->setCurPage((int) $this->getRequest()->getParam('p', 1));
            $this->storyCollection->setPageSize($this->configProvider->getPaginationLimit());
        }
        
        return $this->storyCollection;
    }
    
    public function getMlStoreMediaUrl()
    {
        $store_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $store_url =  $store_url . 'magelearn/story/';
        
        return $store_url;
    }
    
    /**
     * Add metadata to page header
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->getNameInLayout()) {
            if ($title = $this->configProvider->getMetaTitle()) {
                $this->pageConfig->getTitle()->set($title);
            }
            
            if ($description = $this->configProvider->getMetaDescription()) {
                $this->pageConfig->setDescription($description);
            }
            
            $this->getPagerHtml();
            
            if ($this->pager && !$this->pager->isFirstPage()) {
                $this->addPrevNext(
                    $this->getUrl('mlstory', ['p' => $this->pager->getCurrentPage() - 1]),
                    ['rel' => 'prev']
                    );
            }
            if ($this->pager && $this->pager->getCurrentPage() < $this->pager->getLastPageNum()) {
                $this->addPrevNext(
                    $this->getUrl('mlstory', ['p' => $this->pager->getCurrentPage() + 1]),
                    ['rel' => 'next']
                    );
            }
        }
            
        return parent::_prepareLayout();
    }
    
    /**
     * Add prev/next pages
     *
     * @param string $url
     * @param array $attributes
     *
     */
    protected function addPrevNext($url, $attributes)
    {
        $this->pageConfig->addRemotePageAsset(
            $url,
            'link_rel',
            ['attributes' => $attributes]
            );
    }
    /**
     * Return Pager for locator page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->getLayout()->getBlock('magelearn.story.pager')) {
            $this->pager = $this->getLayout()->getBlock('magelearn.story.pager');

            return $this->pager->toHtml();
        }
        if (!$this->pager) {
            $this->pager = $this->getLayout()->createBlock(
                Pager::class,
                'magelearn.story.pager'
            );

            if ($this->pager) {
                $this->pager->setUseContainer(
                    false
                )->setShowPerPage(
                    false
                )->setShowAmounts(
                    false
                )->setFrameLength(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setJump(
                    $this->_scopeConfig->getValue(
                        'design/pagination/pagination_frame_skip',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )->setLimit(
                    $this->configProvider->getPaginationLimit()
                )->setCollection(
                    $this->getClearStoryCollection()
                )->setTemplate(
                    'Magelearn_Story::pager.phtml'
                );

                return $this->pager->toHtml();
            }
        }

        return '';
    }
    
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [StoryModel::CACHE_TAG];
    }
    
    /**
     * @return string[]
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        if ($this->configProvider->getPaginationLimit()) {
            $cacheKeyInfo = array_merge(
                $cacheKeyInfo,
                [implode('-', $this->getClearStoryCollection()->getIdsOnPage())]
            );
        }

        return $cacheKeyInfo;
    }
}