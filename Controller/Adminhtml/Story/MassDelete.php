<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller\Adminhtml\Story;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magelearn\Story\Model\ResourceModel\Story\CollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class MassDelete
 * @package Magelearn\Story\Controller\Adminhtml\Story
 */
class MassDelete extends Action implements HttpPostActionInterface
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
    
    /**
     * Execute Mass Delete Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->delete();
        }
        
        $this->messageManager->addSuccessMessage(__('A total of %1 questions have been deleted.', $collectionSize));
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}