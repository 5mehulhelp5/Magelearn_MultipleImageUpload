<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller\Story;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magelearn\Story\Model\Story;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class View
 */
class View extends Action
{
    /**
     * @var Story
     */
    private $storyModel;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Context $context,
        Story $storyModel,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->storyModel = $storyModel;
        $this->coreRegistry = $coreRegistry;
    }
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $urlKey = $this->getRequest()->getParam('url_key');

        if ($storyId = (int)$this->_request->getParam('id')) {
            $story = $this->storyModel->load($storyId);
        }
        if (!$storyId) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        }
        $this->coreRegistry->register('mlstory_current_story', $story);
        $this->coreRegistry->register('mlstory_current_story_id', $story->getId());

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
