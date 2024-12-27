<?php
declare(strict_types=1);

namespace Magelearn\Story\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $path . 'magelearn/story/'. $name;
    }

    public function compressHtml($html)
    {
        return preg_replace(
            '#(?ix)(?>[^\S ]\s*|\s{2,})#', //remove break lines
            ' ',
            preg_replace('/<!--(?!\s*ko\s|\s*\/ko)[^>]*-->/', '', $html) //remove html comments
        );
    }
}
