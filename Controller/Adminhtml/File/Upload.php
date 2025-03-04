<?php
declare(strict_types=1);

namespace Magelearn\Story\Controller\Adminhtml\File;

use Magento\Framework\Controller\ResultFactory;
use Magelearn\Story\Model\ImageProcessor;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ImageUploader;

/**
 * Class Upload
 */
class Upload extends Action
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Upload file controller action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $imageType = $this->getRequest()->getParam('type');
            $storyId = (int)$this->getRequest()->getParam('id');
            $this->imageUploader->setBaseTmpPath(
                ImageProcessor::ML_STORY_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $storyId
            );
            $result = $this->imageUploader->saveFileToTmpDir($imageType);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
