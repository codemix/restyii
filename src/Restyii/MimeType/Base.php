<?php

namespace Restyii\MimeType;

use Restyii\Model\ActiveDataProvider;
use Restyii\Model\ActiveRecord;
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
        if ($data instanceof ActiveDataProvider)
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
     * @param ActiveDataProvider $dataProvider the data provider to prepare
     *
     * @return array the prepared response
     */
    public function prepareActiveDataProvider(ActiveDataProvider $dataProvider)
    {
        $data = array_map(array($this, 'prepare'), $dataProvider->getData());
        $pagination = $dataProvider->getPagination();
        $model = $dataProvider->model;
        if ($model instanceof ModelInterface)
            $containerName = $model->controllerID();
        else
            $containerName = lcfirst(get_class($model));
        $prepared = array(
            'total' => (int) $dataProvider->getTotalItemCount(),
            'limit' => $pagination ? $pagination->getPageSize() : null,
            'currentPage' => $pagination ? $pagination->getCurrentPage() + 1 : null,
            'params' => $dataProvider->getParams(),
            '_embedded' => array(
                $containerName => $data
            ),
            '_links' => array()
        );
        $prepared['_links'] = $dataProvider->getLinks();
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
        $links = $model->links();
        foreach($model->getVisibleAttributeNames() as $name)
            $prepared[$name] = $model->{$name};

        if ($model instanceof ActiveRecord) {
            $embedded = array();
            foreach($model->getMetaData()->relations as $name => $config){
                if (!$model->hasRelated($name))
                    continue;
                $embedded[$name] = $this->prepare($model->getRelated($name));
            }
            if (count($embedded))
                $prepared['_embedded'] = $embedded;
        }

        if ($model->hasErrors())
            $prepared['_errors'] = $model->getErrors();
        if ($model->getIsDeleted())
            $prepared['_deleted'] = true;
        $prepared['_links'] = $links;
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
