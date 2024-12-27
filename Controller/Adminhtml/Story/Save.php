<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller\Adminhtml\Story;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    
    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var \Magelearn\Story\Model\Story
     */
    protected $storyModel;
    
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionModel;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    
    /**
     * @var \Magelearn\Story\Model\ResourceModel\Story\Collection
     */
    protected $storyCollection;
    
    /**
     * @var DateTime
     */
    public $date;
    
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magelearn\Story\Model\Story $storyModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magelearn\Story\Model\ResourceModel\Story\Collection $storyCollection,
        DateTime $date,
        TimezoneInterface $timezone
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->ioFile = $ioFile;
        $this->storyModel = $storyModel;
        $this->sessionModel = $context->getSession();
        $this->logger = $logger;
        $this->filter = $filter;
        $this->storyCollection = $storyCollection;
        $this->date         = $date;
        $this->timezone     = $timezone;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            $id = (int)$this->getRequest()->getParam('id');
        
            $model = $this->storyModel->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Story no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            
            if (isset($data['stores']) && !array_filter($data['stores'])) {
                $data['stores'] = ',0,';
            }
            if (isset($data['stores']) && is_array($data['stores'])) {
                $data['stores'] = ',' . implode(',', array_filter($data['stores'])) . ',';
            }

            if ($model->getCreatedAt() == null) {
                $data['created_at'] = $this->date->date();
            }
            $data['updated_at'] = $this->date->date();
            
            $this->filterData($data);
            
            $this->storyModel->addData($data);
            
            $this->_prepareForSave($this->storyModel);
            
            $session = $this->sessionModel->setPageData($this->storyModel->getData());
            
            try {
                $this->storyModel->save();
                $this->messageManager->addSuccessMessage(__('You saved the Story.'));
                $this->dataPersistor->clear('magelearn_story_story');
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Story.'));
            }
        
            $this->dataPersistor->set('magelearn_story_story', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    /**
     * @param array $data
     */
    private function filterData(&$data)
    {
        if (isset($data['photo']) && is_array($data['photo'])) {
            if (isset($data['photo'][0]['name'])) {
                $data['photo'] = $data['photo'][0]['name'];
            }
        } else {
            $data['photo'] = null;
        }
    }
    
    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();

        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
            )->getAbsolutePath(
                'magelearn/story/'
                );
            
            $imagesTypes = ['store', 'photo'];
            foreach ($imagesTypes as $type) {
                $field = $type . '_img';

                $files = $this->getRequest()->getFiles();
                
                $isRemove = isset($data['remove_' . $field]);
                $fileData = $this->getRequest()->getFiles($field);
                $hasNew = !empty($fileData['name']);
                
                try {
                    // remove the old file
                    if ($isRemove || $hasNew) {
                        $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                        if ($oldName) {
                            $this->ioFile->rm($path . $oldName);
                            $model->setData($field, '');
                        }
                    }
                    
                    // upload a new if any
                    if (!$isRemove && $hasNew) {
                        //find the first available name
                        $storyId = $model->getId();
                        $newName = $storyId . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                        if (substr($newName, 0, 1) == '.') {
                            $newName = 'label' . $newName;
                        }
                        $uploader = $this->fileUploaderFactory->create(['fileId' => $field]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->save($path, $newName);
                        
                        $model->setData($field, $newName);
                    }
                } catch (\Exception $e) {
                    if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                        $this->logger->critical($e);
                    }
                }
            }
            
            return true;
    }
}

