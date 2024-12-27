<?php
declare(strict_types=1);

namespace Magelearn\Story\Model\ResourceModel\Story;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magelearn\Story\Api\Data\StoryInterface;
use Magelearn\Story\Model\ConfigProvider;
use Magelearn\Story\Model\ResourceModel\Gallery;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var Request
     */
    protected $httpRequest;
    
    /**
     * @var ConfigProvider
     */
    private $configProvider;
    
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Registry $registry,
        ScopeConfigInterface $scope,
        Request $httpRequest,
        ConfigProvider $configProvider,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
        ) {
            $this->storeManager = $storeManager;
            $this->request = $request;
            $this->coreRegistry = $registry;
            $this->scopeConfig = $scope;
            $this->httpRequest = $httpRequest;
            $this->configProvider = $configProvider;
            parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magelearn\Story\Model\Story::class,
            \Magelearn\Story\Model\ResourceModel\Story::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
    
    /**
     * Apply filters to story collection
     *
     * @throws NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        
        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }
        $select->where('main_table.status = 1');
        $select->order('main_table.position ASC');
        $select->order('main_table.id ASC');

        $select->order(sprintf('main_table.id %s', Select::SQL_ASC));
    }
    
    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);
        
        return $this;
    }
    
    /**
     * Get SQL for get record count
     *
     * @return Select $countSelect
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Select::COLUMNS);
        $columns = array_merge($select->getPart(Select::COLUMNS), $this->getSelect()->getPart(Select::COLUMNS));
        $select->setPart(Select::COLUMNS, $columns);
        $countSelect = $this->getConnection()->select()
        ->from($select)
        ->reset(Select::COLUMNS)
        ->columns(new \Zend_Db_Expr(("COUNT(*)")));
        
        return $countSelect;
    }
    
    /**
     * @param array $storeIds
     * @return Select
     */
    public function addFilterByStores($storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `main_table`.`stores`)';
        }
        
        $where = implode(' OR ', $where);
        
        return $this->getSelect()->where($where);
    }

    /**
     * Get story data
     *
     * @return array $storyArray
     */
    public function getStoryData()
    {
        $storyArray = [];
        
        foreach ($this->getItems() as $story) {
            /** @var \Magelearn\Story\Model\Story $story */

            $story = $story->getData();
            $storyArray[] = $story;
        }
        
        return $storyArray;
    }
    
    /**
     * Get Base Image
     *
     * @return $this
     */
    public function joinMainImage()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['img'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['img' => $this->getTable(Gallery::TABLE_NAME)],
            'main_table.id = img.story_id AND img.is_base = 1',
            ['main_image_name' => 'img.image_name']
            );
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getAllIds()
    {
        return \Magento\Framework\Data\Collection::getAllIds();
    }
    
    public function getIdsOnPage(): array
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}

