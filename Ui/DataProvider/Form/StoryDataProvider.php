<?php
declare(strict_types=1);

namespace Magelearn\Story\Ui\DataProvider\Form;

use Magelearn\Story\Model\ResourceModel\Story\Collection;
use Magelearn\Story\Model\ImageProcessor;
use Magelearn\Story\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Magento\Framework\App\RequestInterface;

class StoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        ImageProcessor $imageProcessor,
        GalleryCollection $galleryCollection,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->imageProcessor = $imageProcessor;
        $this->galleryCollection = $galleryCollection;
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        /**
         * It is need for support of several fieldsets.
         * For details @see \Magento\Ui\Component\Form::getDataSourceData
         */
        if ($data['totalRecords'] > 0) {
            $storyId = (int)$data['items'][0]['id'];
            $storyModel = $this->collection->getItemById($storyId);

            /** @var \Magelearn\Story\Model\ResourceModel\Story $storyResource */
            $storyResource = $storyModel->getResource();
            $storyData = $storyModel->getData();

            if ($storyModel->getPhoto()) {
                $photoName = $storyModel->getPhoto();
                $storyData['photo'] = [
                    [
                        'name' => $storyModel->getPhoto(),
                        'url' => $this->imageProcessor->getImageUrl(
                            [ImageProcessor::ML_STORY_MEDIA_PATH, $storyData['id'], $photoName]
                        )
                    ]
                ];
            }
            $galleryImages = $this->galleryCollection->getImagesByStory($storyId);
            if (!empty($galleryImages)) {
                $storyData['gallery_image'] = [];

                foreach ($galleryImages as $image) {
                    $imgName = $image->getData('image_name');
                    $imgUrlParams = [ImageProcessor::ML_STORY_GALLERIES_MEDIA_PATH, $storyData['id'], $imgName];
                    $imgUrl = $this->imageProcessor->getImageUrl($imgUrlParams);
                    $imgSize = $this->imageProcessor->getImageSize($imgUrlParams);

                    array_push(
                        $storyData['gallery_image'],
                        ['name' => $imgName, 'url' => $imgUrl, 'size' => $imgSize]
                    );

                    if ($image->getData('is_base') == true) {
                        $storyData['base_img'] = $imgName;
                    }
                }
            }
            $data[$storyId] = $storyData;
        }

        return $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        $storyId = (int)$this->request->getParam('id');
        $this->meta['general']['children']['photo']['arguments']['data']['config']['uploaderConfig']['url'] =
        'magelearn_story/file/upload/type/photo/id/' . $storyId;

        $this->meta['image_gallery']['children']['gallery']['arguments']['data']['config']['uploaderConfig']['url'] =
        'magelearn_story/file/upload/type/gallery_image/id/' . $storyId;

        return $this->meta;
    }
}
