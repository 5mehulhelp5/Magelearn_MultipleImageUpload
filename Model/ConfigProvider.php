<?php
declare(strict_types=1);

namespace Magelearn\Story\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Scope config Provider model
 */
class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    public const PATH_PREFIX = 'mlstory';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const XPATH_NEW_PAGE = 'general/new_page';
    public const XPATH_LINK_TEXT = 'general/linktext';
    public const XPATH_ENABLE_PAGES = 'general/enable_pages';
    public const XPATH_LABEL = 'general/label';
    public const XPATH_ADD_LINK = 'general/add_to_toolbar_menu';

    public const META_TITLE = 'story/main_settings/meta_title';
    public const META_DESCRIPTION = 'story/main_settings/meta_description';
    public const XPATH_PAGINATION_LIMIT = 'story/main_settings/pagination_limit';
    public const XPATH_URL = 'story/main_settings/url';
    public const XPATH_DESCRIPTION_LIMIT = 'story/main_settings/description_limit';


    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaTitle($scopeCode = null)
    {
        return $this->getValue(self::META_TITLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaDescription($scopeCode = null)
    {
        return $this->getValue(self::META_DESCRIPTION, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getPaginationLimit($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_PAGINATION_LIMIT, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getOpenNewPage($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_NEW_PAGE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getLinkText($scopeCode = null)
    {
        $linkText = $this->getValue(self::XPATH_LINK_TEXT, $scopeCode);

        return $linkText ? $linkText : __('Available In Story')->getText();
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getUrl($scopeCode = null)
    {
        return $this->getValue(self::XPATH_URL, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getEnablePages($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_ENABLE_PAGES, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getDescriptionLimit($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_DESCRIPTION_LIMIT, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getLabel($scopeCode = null)
    {
        $label = $this->getValue(self::XPATH_LABEL, $scopeCode);

        return $label ? $label : __('Story Listing')->getText();
    }

    /**
     * @param null $scopeCode
     *
     * @return bool
     */
    public function isAddLinkToToolbar($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_ADD_LINK, $scopeCode);
    }
}
