<?php

namespace Restyii\MimeType;

use Restyii\Model\ActiveDataProvider;
use Restyii\Model\ActiveRecord;
use Restyii\Model\DataProviderInterface;
use Restyii\Model\ModelInterface;
use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as Markdown.
 *
 * @package Restyii\MimeType
 */
class Markdown extends Base
{

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "md",
        "markdown",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "text/markdown",
        "text/plain",
    );

    /**
     * @inheritDoc
     */
    public function canParse(Request $request)
    {
        return false;
    }


    /**
     * Parse the given input and return either an array of attributes => values
     * or null if the input could not be parsed.
     *
     * @param Request $request the request object
     *
     * @return array|null
     */
    public function parse(Request $request)
    {
        return null;
    }

    /**
     * MimeType the given request response
     *
     * @param Response $response the response to format
     *
     * @return string the formatted response data
     */
    public function format(Response $response)
    {
        return $this->prepare($response->data);
    }
    /**
     * @inheritDoc
     */
    public function prepare($data)
    {
        if ($data instanceof DataProviderInterface)
            return $this->prepareActiveDataProvider($data);
        else if ($data instanceof ModelInterface)
            return $this->prepareModel($data);
        else if (is_array($data))
            return implode("\n", array_map(array($this, 'prepare'), $data));
        else
            return (string) $data;
    }

    /**
     * @inheritDoc
     */
    public function prepareActiveDataProvider(DataProviderInterface $dataProvider)
    {
        /* @var DataProviderInterface|ActiveDataProvider $dataProvider */
        /* @var \CModel|\Restyii\Model\ModelInterface $model */
        $out = array();
        $model = $dataProvider->model;
        if ($model instanceof ModelInterface) {
            $out[] = "# ".$model->classLabel(true)."\n";

            $total = $dataProvider->getTotalItemCount();
            $out[] = \Yii::t('resource', "n==0#There are no {collectionLabel}.|n==1#There is one {resourceLabel}.|n>1#There are {n} {collectionLabel}.", array(
                $total,
                '{collectionLabel}' => $model->classLabel(true),
                '{resourceLabel}' => $model->classLabel(),
            ));

        }
        else {
            $out[] = "# ".get_class($model)."\n";

        }

        $out[] = "\n";

        foreach($dataProvider->getData() as $item)
            $out[] = $this->prepare($item);

        return implode("\n", $out);
    }

    /**
     * @inheritDoc
     */
    public function prepareModel(ModelInterface $model)
    {
        $out = array();
        $name = $model->instanceLabel();
        if (empty($name) && $model instanceof ActiveRecord)
            $name = $model->getPrimaryKey();

        $out[] = "# ".\Yii::t('resource', "{resourceLabel}: {resourceName}",
                array('{resourceLabel}' => $model->classLabel(), '{resourceName}' => $name))."\n";
        $attributeNames = $model->getVisibleAttributeNames();
        foreach($attributeNames as $name) {
            if (!empty($model->{$name}))
                $out[] = "__".$model->getAttributeLabel($name)."__ `".$model->{$name}."`\n";
        }
        return implode("\n", $out)."\n---\n";
    }
}
