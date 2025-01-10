<?php

namespace Magelearn\Story\Controller\Adminhtml\Story;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magelearn\Story\Model\ResourceModel\Story\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class MassAction extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        parent::__construct($context);
        
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
        /** @var \Magento\Ui\Component\MassAction\Filter $filter */
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var $collection \Magelearn\Story\Model\ResourceModel\Story\CollectionFactory */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $collectionSize = $collection->getSize();
        $action = $this->getRequest()->getParam('action');
        if ($collectionSize && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $collection->walk($action);
                if ($action === 'delete') {
                    $this->messageManager->addSuccessMessage(__('You deleted the story(s).'));
                } else {
                    $this->messageManager->addSuccessMessage(__('You changed the story(s).'));
                }

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete story(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->logger->critical($e);

                return $resultRedirect;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a story(s) to delete.'));

        return $resultRedirect;
    }
}
