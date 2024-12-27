<?php
declare(strict_types=1);

namespace Magelearn\Story\Block;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Return correct URL for ajax request
     *
     * @param array $params
     * @return string
     */
    public function getPagerUrl($params = [])
    {
        $ajaxUrl = $this->_urlBuilder->getUrl('mlstory/index/ajax');
        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        return $ajaxUrl . '?' . http_build_query($params);
    }
}
