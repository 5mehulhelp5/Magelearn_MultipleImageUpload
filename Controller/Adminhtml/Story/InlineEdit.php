<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller\Adminhtml\Story;

use Magento\Backend\App\Action\Context;
use Magelearn\Story\Model\StoryFactory;

class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var StoryFactory
     */
    private $storyFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param StoryFactory $storyFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        StoryFactory $storyFactory
    ) {
        parent::__construct($context);
        $this->storyFactory = $storyFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($postItems as $storyId => $storyData) {
            /** @var \Magelearn\Story\Model\Story $story */
            $story = $this->storyFactory->create();
            $story->load($storyId);
            $story->setData('inlineEdit', true);
            try {
                $story->addData($storyData);
                $story->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorMessage($location, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorMessage($location, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorMessage(
                    $location,
                    __('Something went wrong while saving the location.')
                    );
                $error = true;
            }
        }
        
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add story id to error message
     *
     * @param \Magelearn\Story\Model\Story $story
     * @param string $errorText
     * @return string
     */
    private function getErrorMessage($story, $errorText)
    {
        return '[Story ID: ' . $story->getId() . '] ' . $errorText;
    }
}

