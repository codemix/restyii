<?php

namespace Restyii\MimeType;

use Restyii\Model\ModelInterface;
use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Base class for mime types
 * @package Restyii\MimeType\Base
 */
abstract class Base extends \CComponent
{

    /**
     * @var array the file extensions this format accepts
     */
    public $fileExtensions = array();

    /**
     * @var array the mime types this format accepts.
     */
    public $mimeTypes = array();

    /**
     * Determine whether or not the type can parse a given request.
     *
     * @param Request $request the request to check
     *
     * @return bool true if the formatter can parse the request, otherwise false.
     */
    public function canParse(Request $request)
    {
        $contentType = $request->getContentType();
        return in_array($contentType, $this->mimeTypes);
    }

    /**
     * Determine whether or not the type can format a given response.
     *
     * @param Response $response the response to check
     *
     * @return bool true if the formatter can format the response, otherwise false.
     */
    public function canFormat(Response $response)
    {
        $request = $response->getRequest();
        $fileExtension = $request->getFileExtension();
        foreach($this->fileExtensions as $ext)
            if ($ext == $fileExtension)
                return true;

        $acceptTypes = $request->getPreferredAcceptTypes();
        foreach($acceptTypes as $preferredType) {
            $preferredString = $preferredType['type'];
            if (!empty($preferredType['subType']))
                $preferredString .= '/'.$preferredType['subType'];
            if (!empty($preferredType['baseType']))
                $preferredString .= '+'.$preferredType['baseType'];
            foreach($this->mimeTypes as $type) {
                if (strcasecmp($type, $preferredString) === 0)
                    return true;
            }
        }
        return false;
    }

    /**
     * Parse the given input and return either an array of attributes => values
     * or null if the input could not be parsed.
     * @param Request $request the request object
     *
     * @return array|null
     */
    abstract public function parse(Request $request);


    /**
     * MimeType the given request response
     * @param Response $response the response to format
     *
     * @return string the formatted response data
     */
    abstract public function format(Response $response);


    /**
     * Prepares a data item to be sent in a response
     * @param mixed $data the data to prepare
     *
     * @return array|mixed
     */
    public function prepare($data)
    {
        if ($data instanceof \CActiveDataProvider)
            return $this->prepareActiveDataProvider($data);
        else if ($data instanceof ModelInterface)
            return $this->prepareModel($data);
        else if ($data instanceof \Exception)
            return $this->prepareException($data);
        else if (is_array($data))
            return array_map(array($this, 'prepare'), $data);
        else
            return $data;
    }

    /**
     * Prepares a data provider
     *
     * @param \CActiveDataProvider $dataProvider the data provider to prepare
     *
     * @return array the prepared response
     */
    public function prepareActiveDataProvider(\CActiveDataProvider $dataProvider)
    {
        $data = array_map(array($this, 'prepare'), $dataProvider->getData());
        $pagination = $dataProvider->getPagination();
        $model = $dataProvider->model;
        if ($model instanceof ModelInterface)
            $containerName = $model->urlName();
        else
            $containerName = lcfirst(get_class($model));
        $prepared = array(
            'total' => (int) $dataProvider->getTotalItemCount(),
            'offset' => $pagination ? $pagination->getOffset() : 0,
            'limit' => $pagination ? $pagination->getLimit() : null,
            '_embedded' => array(
                $containerName => $data
            ),
            '_links' => array()
        );
        if (method_exists($dataProvider, 'links')) {
            $prepared['_links'] = $dataProvider->links();
        }
        else if (isset($dataProvider->links)) {
            $prepared['_links'] = $dataProvider->links;
        }
        else if ($pagination) {
            $controller = \Yii::app()->getController();
            if ($pagination->getCurrentPage() > 0)
                $prepared['_links']['prev'] = array(
                    'label' => 'Previous Page',
                    'href' => $controller->createUrl(
                        $controller->getRoute(),
                        \CMap::mergeArray(
                            $_GET,
                            array(
                                'offset' => $pagination->getOffset() - $pagination->getLimit(),
                            )
                        )
                    ),
                );
            if ($pagination->getCurrentPage() < ($pagination->getPageCount() - 1))
                $prepared['_links']['prev'] = array(
                    'label' => 'Next Page',
                    'href' => $controller->createUrl(
                        $controller->getRoute(),
                        \CMap::mergeArray(
                            $_GET,
                            array(
                                'offset' => $pagination->getOffset() + $pagination->getLimit(),
                            )
                        )
                    ),
                );
        }
        return $prepared;
    }

    /**
     * Prepare a resource model
     *
     * @param ModelInterface $model the resource to prepare
     *
     * @return array the prepared response
     */
    public function prepareModel(ModelInterface $model)
    {
        $prepared = array();
        foreach($model->getVisibleAttributeNames() as $name)
            $prepared[$name] = $model->{$name};
        if ($model->hasErrors())
            $prepared['_errors'] = $model->getErrors();
        $prepared['_links'] = $model->links();
        return $prepared;
    }

    /**
     * Prepare an exception
     *
     * @param \Exception $exception the exception to prepare
     *
     * @return array the prepared response
     */
    public function prepareException(\Exception $exception)
    {
        $prepared = array(
            'error' => array(
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            )
        );

        return $prepared;
    }
}
