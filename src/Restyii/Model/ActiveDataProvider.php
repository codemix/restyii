<?php


namespace Restyii\Model;

/**
 * Class ActiveDataProvider
 *
 * @property ActiveRecord $model the model used for searching
 *
 * @package Restyii\Model
 */
class ActiveDataProvider extends \CActiveDataProvider implements DataProviderInterface
{
    /**
     * @var array the parameters for the data provider
     */
    protected $_params = array();

    /**
     * Gets the parameters for the data provider.
     * These are usually the parameters that were passed to the
     * model's `search()` method.
     *
     * @return array the parameters for the data provider
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Sets the parameters for the data provider.
     *
     * @param array $params the parameters for the data provider.
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    /**
     * Gets the appropriate links for the data provider.
     *
     * @return array the appropriate links for the data provider
     */
    public function getLinks()
    {
        $controller = \Yii::app()->getController();
        if (($pagination = $this->getPagination()) !== false) {
            $totalPages = $pagination->getPageCount();
            $currentPage = $pagination->getCurrentPage();
            $links = array(
                'self' => array(
                    'title' => $this->model->classLabel(true),
                    'href' => $pagination->createPageUrl($controller, 0),
                ),
                'page' => array(
                    'title' => \Yii::t('resource', 'Page'),
                    'href' => str_replace('999', '{page}', $pagination->createPageUrl($controller, 998)),
                    'templated' => true,
                ),
                'firstPage' => array(
                    'title' => \Yii::t('resource', 'First Page'),
                    'href' => $pagination->createPageUrl($controller, 0),
                ),
            );

            if ($currentPage > 0) {
                $links['prevPage'] = array(
                    'title' => \Yii::t('resource', 'Previous Page'),
                    'href' => $pagination->createPageUrl($controller, $currentPage - 1),
                );
            }
            if ($totalPages < $currentPage + 2) {
                $links['nextPage'] = array(
                    'title' => \Yii::t('resource', 'Next Page'),
                    'href' => $pagination->createPageUrl($controller, $currentPage + 1),
                );
            }

            $links['lastPage'] = array(
                'title' => \Yii::t('resource', 'Last Page'),
                'href' => $pagination->createPageUrl($controller, $totalPages - 1),
            );
        }
        else {
            $links = array(
                'self' => array(
                    'title' => $this->model->classLabel(true),
                    'href' => $controller->createUrl($controller->getRoute(), $this->getParams()),
                ),
            );
        }
        return $links;
    }

}
