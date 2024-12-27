<?php
declare(strict_types=1);

namespace Magelearn\Story\Model;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class ConfigHtmlConverter
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Story
     */
    private $story;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BaseImageStory
     */
    private $baseImageStory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magelearn\Story\Helper\Data
     */
    private $dataHelper;

    /**
     * @var array
     */
    private $countryNameByCode = [];

    /**
     * @var array
     */
    private $stateNameByCode = [];

    public function __construct(
        ConfigProvider $configProvider,
        Escaper $escaper,
        FilterProvider $filterProvider,
        LoggerInterface $logger,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        UrlInterface $urlBuilder,
        BaseImageStory $baseImageStory,
        \Magelearn\Story\Helper\Data $dataHelper
    ) {
        $this->configProvider = $configProvider;
        $this->escaper = $escaper;
        $this->filterProvider = $filterProvider;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->baseImageStory = $baseImageStory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Story $story
     */
    public function setHtml($story)
    {
        $this->story = $story;
        $this->story->setPhotoUrl($this->baseImageStory->getMainImageUrl($story));
        $this->story->setPhoto($this->baseImageStory->getPhotoImageUrl($story));
    }

    /**
     * Get prepared value by key
     *
     * @param string $key
     * @return string
     */
    private function getPreparedValue($key)
    {
        $preparedKey = 'prepared_' . $key;
        if (!$this->story->hasData($preparedKey)) {
            $this->story->setData($preparedKey, $this->prepareValue($key));
        }
        return $this->story->getData($preparedKey);
    }

    /**
     * @param string $key
     * @return string
     */
    private function prepareValue($key)
    {
        switch ($key) {
            case 'name':
                if ($this->story->getUrlKey() && $this->configProvider->getEnablePages()) {
                    return '<div class="mlstory-title"><a class="mlstory-link" href="' . $this->getStoryUrl()
                    . '" title="' . $this->escaper->escapeHtml($this->story->getData($key))
                        . '" target="_blank">'
                            . $this->escaper->escapeHtml($this->story->getData($key)) . '</a></div>';
                }

                return '<div class="mlstory-title">' . $this->escaper->escapeHtml($this->story->getData($key))
                    . '</div>';
            case 'description':
                return $this->getPreparedDescription($key, true);
            case 'short_description':
                return $this->getPreparedDescription($key);
            case 'photo':
                $photo = $this->story->getData($key);

                return '<div class="mlstory-image"><img src="' . $this->escaper->escapeUrl($photo) . '"></div>';
            default:
                return $this->escaper->escapeHtml($this->story->getData($key));
        }
    }

    /**
     * Get prepared description
     *
     * @return string
     */
    public function getPreparedDescription($key, bool $useFilterProcessor = false)
    {
        $descriptionLimit = $this->configProvider->getDescriptionLimit();

        $description = $this->story->getData($key);
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
                $description .= '<a href="' . $this->getStoryUrl() . '" title="read more" target="_blank"> '
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
    private function getStoryUrl()
    {
        return $this->escaper->escapeUrl(
            $this->urlBuilder->getUrl($this->configProvider->getUrl() . '/' . $this->story->getUrlKey())
        );
    }
}
