<?php
declare(strict_types=1);

namespace Magelearn\Story\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;
use Magelearn\Story\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Magelearn\Story\Model\GalleryFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;

class Story extends AbstractDb
{
    public const TABLE_NAME = 'magelearn_story';
    
    /**
     * @var GalleryCollection
     */
    private $galleryCollection;
    
    /**
     * @var Gallery
     */
    private $galleryResource;
    
    /**
     * @var GalleryFactory
     */
    private $galleryFactory;
    
    /**
     * @var \Magelearn\Story\Model\ImageProcessor
     */
    private $imageProcessor;
    
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magelearn\Story\Model\ImageProcessor $imageProcessor,
        GalleryCollection $galleryCollection,
        GalleryFactory $galleryFactory,
        Gallery $galleryResource,
        StoreManagerInterface $storeManager,
        $connectionName = null
        ) {
            parent::__construct($context, $connectionName);
            $this->imageProcessor = $imageProcessor;
            $this->galleryCollection = $galleryCollection;
            $this->galleryFactory = $galleryFactory;
            $this->galleryResource = $galleryResource;
            $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
    
    /**
     * Perform actions before object save
     * @param AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (($object->getOrigData('photo') && $object->getOrigData('photo') != $object->getPhoto())) {
            $this->imageProcessor->deleteImage($object->getOrigData('photo'));
            $object->setPhoto($object->getPhoto() ? $object->getPhoto() : '');
        }
        
        return $this;
    }
    
    protected function _beforeDelete(AbstractModel $object)
    {
        //remove story images
        $allImages = $this->galleryCollection->getImagesByStory($object->getId());
        
        foreach ($allImages as $image) {
            $this->galleryResource->delete($image);
        }
        
        //remove photo image
        if ($photoImg = $object->getPhoto()) {
            $this->imageProcessor->setBasePaths(
                \Magelearn\Story\Model\ImageProcessor::PHOTO_IMAGE_TYPE,
                $object->getId(),
                $object->isObjectNew()
                );
            $this->imageProcessor->deleteImage($photoImg);
        }
    }

    protected function _afterSave(AbstractModel $object)
    {
        $data = $object->getData();
        
        if ($object->getPhoto() && ($image = $object->getData('photo'))
            && $object->getOrigData('photo') != $object->getPhoto()
            ) {
                $this->imageProcessor->processImage(
                    $object->getPhoto(),
                    \Magelearn\Story\Model\ImageProcessor::PHOTO_IMAGE_TYPE,
                    $object->getId(),
                    $object->isObjectNew()
                    );
            }
            
            if (!($object->getData('inlineEdit') || $object->getData('massAction'))) {
                $this->saveGallery($object->getData(), $object->isObjectNew());
            }
            
            $this->_isPkAutoIncrement = true;
    }
    
    private function saveGallery($data, $isObjectNew = false)
    {
        $storyId = $data['id'];
        $allImages = $this->galleryCollection->getImagesByStory($storyId);
        $baseImgName = isset($data['base_img']) ? $data['base_img'] : '';
        
        if (!isset($data['gallery_image'])) {
            foreach ($allImages as $image) {
                $this->galleryResource->delete($image);
            }
            return;
        }
        $galleryImages = $data['gallery_image'];
        $imagesOfStory = [];
        $isImport = false;
        
        foreach ($allImages as $image) {
            $imagesOfStory[$image->getData('image_name')] = $image;
        }
        
        foreach ($galleryImages as $galleryImage) {
            $isImageNew = isset($galleryImage['tmp_name']);
            if (array_key_exists($galleryImage['name'], $imagesOfStory)) {
                unset($imagesOfStory[$galleryImage['name']]);
                
                if ($isImageNew) {
                    continue;
                }
            }
            if ($isImageNew && isset($galleryImage['name'])) {
                $isImport = true;
                $newImage = $this->galleryFactory->create();
                $newImage->addData(
                    [
                        'story_id' => $storyId,
                        'image_name' => $galleryImage['name'],
                        'is_base' => $baseImgName === $galleryImage['name'],
                        'story_is_new' => $isObjectNew
                    ]
                    );
                $this->galleryResource->save($newImage);
            }
        }
        
        if (!empty($galleryImages) && !$isImport) {
            foreach ($imagesOfStory as $imageToDelete) {
                $this->galleryResource->delete($imageToDelete);
            }
        }
        
        $baseImg = $this->galleryCollection->getByNameAndStory($storyId, $baseImgName);
        
        if (!empty($baseImg->getData())) {
            foreach ($allImages as $image) {
                if ($image->getData('is_base') == true) {
                    $image->addData(['is_base' => false]);
                    $this->galleryResource->save($image);
                }
            }
            $baseImg->addData(['is_base' => true]);
            $this->galleryResource->save($baseImg);
        }
    }
    
    /**
     * Set _isPkAutoIncrement for saving new story
     */
    public function setResourceFlags()
    {
        $this->_isPkAutoIncrement = false;
    }
    
    /**
     * @param string $urlKey
     * @param array $storeIds
     *
     * @return int
     */
    public function matchStoryUrl($urlKey, $storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `stores`)';
        }
        
        $where = implode(' OR ', $where);
        $select = $this->getConnection()->select()
        ->from(['stories' => $this->getMainTable()])
        ->where('stories.url_key = ?', $urlKey)
        ->where($where)
        ->reset(Select::COLUMNS)
        ->columns('stories.id');
        
        return (int)$this->getConnection()->fetchOne($select);
    }
    
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        
        return $select;
    }
}

