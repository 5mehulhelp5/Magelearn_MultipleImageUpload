<?php
declare(strict_types=1);

namespace Magelearn\Story\Model;

class BaseImageStory
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        ImageProcessor $imageProcessor
    ) {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param \Magelearn\Story\Model\Story $story
     *
     * @return string
     */
    public function getMainImageUrl($story)
    {
        $baseImage = $story->getMainImageName();

        if ($baseImage) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::ML_STORY_GALLERIES_MEDIA_PATH, $story->getId(), $baseImage]
            );
        }

        return '';
    }
    
    /**
     * @param \Magelearn\Story\Model\Story $story
     *
     * @return string
     */
    public function getPhotoImageUrl($story)
    {
        $photoImage = $story->getPhoto();

        if ($photoImage) {
            return $this->imageProcessor->getImageUrl(
                [ImageProcessor::ML_STORY_MEDIA_PATH, $story->getId(), $photoImage]
                );
        }
        
        return '';
    }
}
