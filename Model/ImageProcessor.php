<?php
declare(strict_types=1);

namespace Magelearn\Story\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageProcessor
{
    /**
     * Story area inside media folder
     */
    public const ML_STORY_MEDIA_PATH = 'magelearn/story';

    /**
     * Story temporary area inside media folder
     */
    public const ML_STORY_MEDIA_TMP_PATH = 'magelearn/story/tmp';

    /**
     * Gallery area inside media folder
     */
    public const ML_STORY_GALLERIES_MEDIA_PATH = 'magelearn/story/galleries';

    /**
     * Gallery temporary area inside media folder
     */
    public const ML_STORY_GALLERIES_MEDIA_TMP_PATH = 'magelearn/story/galleries/tmp';

    /**
     * Type image option photo
     */
    public const PHOTO_IMAGE_TYPE = 'photo';

    /**
     * Type image option gallery_image
     */
    public const GALLERY_IMAGE_TYPE = 'gallery_image';

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    private $imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    protected $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function getImageRelativePath($imageName)
    {
        return $this->imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    private function getFileMediaPath($params)
    {
        return $this->getMediaDirectory()->stat(implode(DIRECTORY_SEPARATOR, $params));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getImageSize($params)
    {
        $fileHandler = $this->getFileMediaPath($params);

        return $fileHandler['size'] ?? 0;
    }

    /**
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getImageUrl($params = [])
    {
        return $this->getMediaUrl() . implode(DIRECTORY_SEPARATOR, $params);
    }

    /**
     * Move file from temporary directory
     *
     * @param string $imageName
     * @param string $imageType
     * @param int $storyId
     * @param bool $storyIsNew
     */
    public function processImage($imageName, $imageType, $storyId, $storyIsNew)
    {
        $this->setBasePaths($imageType, $storyId, $storyIsNew);
        $this->imageUploader->moveFileFromTmp($imageName, true);

        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        try {
            $this->prepareImage($filename, $imageType);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->messageManager->addErrorMessage(
                __($errorMessage)
            );
            $this->logger->critical($e);
        }
    }

    /**
     * @param string $filename
     * @param string $imageType
     * @param bool $needResize
     */
    public function prepareImage($filename, $imageType, $needResize = false)
    {
        /** @var \Magento\Framework\Image $imageProcessor */
        $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
        $imageProcessor->keepAspectRatio(true);
        $imageProcessor->keepFrame(true);
        $imageProcessor->keepTransparency(true);
        /*if ($imageType == self::PHOTO_IMAGE_TYPE || $needResize) {
            $imageProcessor->resize(27, 43);
        }*/
        $imageProcessor->save();
    }

    /**
     * @param string $imageName
     */
    public function deleteImage($imageName)
    {
        if ($imageName && strpos($imageName, '.') !== false) {
            $this->getMediaDirectory()->delete(
                $this->getImageRelativePath($imageName)
            );
        }
    }

    /**
     * @param string $imageType
     * @param int $storyId
     * @param bool $storyIsNew
     */
    public function setBasePaths($imageType, $storyId, $storyIsNew)
    {
        // if story doesn't exist, we set 0 to tmp path
        $tmpStoryId = $storyIsNew ? 0 : $storyId;
        $tmpPath = ImageProcessor::ML_STORY_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $tmpStoryId;
        $this->imageUploader->setBaseTmpPath(
            $tmpPath
        );
        switch ($imageType) {
            case ImageProcessor::PHOTO_IMAGE_TYPE:
                $this->imageUploader->setBasePath(
                ImageProcessor::ML_STORY_MEDIA_PATH . DIRECTORY_SEPARATOR . $storyId
                );
                break;

            case ImageProcessor::GALLERY_IMAGE_TYPE:
                $this->imageUploader->setBasePath(
                ImageProcessor::ML_STORY_GALLERIES_MEDIA_PATH . DIRECTORY_SEPARATOR . $storyId
                );
                break;
        }
    }
}
