<?php


namespace Restyii\MimeType;

use Restyii\Model\ModelInterface;
use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as Comma Separated Values (CSV).
 *
 * @package Restyii\MimeType
 */
class CSV extends Base
{

    /**
     * @var string the field delimiter character
     */
    public $delimiter = ',';

    /**
     * @var string the enclosure character
     */
    public $enclosure = '"';

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "csv",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "text/csv",
    );

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
        $input = $request->getRawBody();
        if (empty($input))
            return null;
        ini_set('auto_detect_line_endings', true);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);

        $data = array();
        $names = null;
        $totalRows = 0;
        while(($row = fgetcsv($stream, 4096, $this->delimiter, $this->enclosure)) !== false) {
            if ($names === null)
                $names = $row;
            else {
                $item = array();
                foreach($row as $key => $value) {
                    if (!isset($names[$key]))
                        break;
                    $item[$names[$key]] = $value;
                }
                $data[] = $item;
                $totalRows++;
            }
        }
        fclose($stream);
        if ($totalRows === 0)
            return null;
        if ($totalRows === 1)
            $data = $data[0];

        return $data;
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
        $stream = fopen('php://memory', 'w+');

        $prepared = $this->prepare($response->data);
        foreach($prepared as $row) {
            fputcsv($stream, $row, $this->delimiter, $this->enclosure);
        }
        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function prepare($data)
    {
        if ($data instanceof \CActiveDataProvider)
            return $this->prepareActiveDataProvider($data);
        else if ($data instanceof ModelInterface)
            return $this->prepareModel($data);
        else if (!is_array($data))
            return array($data);
        else
            return array();
    }

    /**
     * @inheritDoc
     */
    public function prepareActiveDataProvider(\CActiveDataProvider $dataProvider)
    {
        $model = $dataProvider->model;
        if ($model instanceof \Restyii\Model\ModelInterface)
            $names = $model->getVisibleAttributeNames();
        else
            $names = $model->attributeNames();
        $rows = array($names);
        foreach($dataProvider->getData() as $item) {
            $row = array();
            foreach($names as $name)
                $row[] = $item->{$name};
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @inheritDoc
     */
    public function prepareModel(ModelInterface $model)
    {
        $names = $model->getVisibleAttributeNames();
        $row = array();
        foreach($names as $name)
            $row[] = $model->{$name};
        if ($model->hasErrors()) {
            foreach($model->getErrors() as $attribute => $errors) {
                $names[] = $attribute.'_ERRORS';
                $row[] = implode(' and ', $errors);
            }

        }
        return array($names, $row);
    }


}
