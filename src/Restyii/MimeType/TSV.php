<?php


namespace Restyii\MimeType;

use Restyii\Model\ModelInterface;
use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as Tab Separated Values (TSV).
 *
 * @package Restyii\MimeType
 */
class TSV extends CSV
{

    /**
     * @var string the field delimiter character
     */
    public $delimiter = "\t";

    /**
     * @var string the enclosure character
     */
    public $enclosure = '"';

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "tsv",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "text/tsv",
    );

}
