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
        $ajaxUrl = rtrim($this->_urlBuilder->getUrl('mlstory'), '/');

        if ($query = $this->getRequest()->getParam('query')) {
            $params['query'] = $query;
        }

        // Remove page parameter if it's 1 or not set
        if (isset($params['p']) && ($params['p'] == 1 || $params['p'] == '1')) {
            unset($params['p']);
        }

        // Return base URL without any parameters if empty
        if (empty($params)) {
            return $ajaxUrl;
        }

        $queryString = http_build_query($params);
        return !empty($queryString) ? $ajaxUrl . '?' . $queryString : $ajaxUrl;
    }
}
