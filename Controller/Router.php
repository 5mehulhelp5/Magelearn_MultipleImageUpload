<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller;

use Magento\Framework\Module\Manager;
use Magento\Store\Model\Store;
use Magelearn\Story\Model\ResourceModel\Story;
use Magelearn\Story\Model\ConfigProvider;

class Router implements \Magento\Framework\App\RouterInterface
{
    public const STORY_CONTROLLER_PATH = 'mlstory';

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Story
     */
    private $storyResource;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Story $storyResource,
        ConfigProvider $configProvider
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storyResource = $storyResource;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
        $storyPage = $this->configProvider->getUrl();

        $identifier = trim($this->request->getPathInfo(), '/');

        $request->setRouteName('mlstory');

        if (strpos($identifier, self::STORY_CONTROLLER_PATH) !== false &&
            !$this->request->isAjax()) {
            $this->request->setModuleName('mlstory')->setControllerName('index')->setActionName('index');
            
            return $this->actionFactory->create(\Magelearn\Story\Controller\Index\Index::class);
        }

        $identifier = current(explode("/", $identifier));
        
        $identifierPart = trim($request->getPathInfo(), '/');
        $parts = explode('/', $identifierPart);

        // Check if the URL matches our story view pattern
        if (count($parts) === 3 && $parts[0] === 'story' && $parts[1] === 'view') {
            $urlKey = $parts[2];
            $stores = [Store::DEFAULT_STORE_ID, $this->storeManager->getStore(true)->getId()];
            if ($storyId = $this->storyResource->matchStoryUrl($urlKey, $stores)) {
                $this->request->setModuleName('mlstory')
                            ->setControllerName('story')
                            ->setActionName('view')
                            ->setParam('id', $storyId)
                            ->setParam('url_key', $urlKey);
                $this->request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                $this->request->setDispatched(true);
                
                return $this->actionFactory->create(\Magelearn\Story\Controller\Story\View::class);
            }
        }
        if ($identifier == $storyPage) {
            $this->request->setDispatched(true);
            $this->request->setModuleName('mlstory')->setControllerName('index')->setActionName('index');
            $this->request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            
            return $this->actionFactory->create(\Magelearn\Story\Controller\Index\Index::class);
        } else {
            return null;
        }

        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
    
    /**
     * @return string
     */
    private function getUrlKey()
    {
        return urldecode(trim(
            str_replace($this->configProvider->getUrl(), '', $this->request->getPathInfo()),
            '/'
            ));
    }
}
