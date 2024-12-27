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
}
