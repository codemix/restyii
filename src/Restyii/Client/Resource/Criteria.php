<?php


namespace Restyii\Client\Resource;

/**
 * # Criteria
 *
 * Represents a search criteria, similar to `\CDbCriteria`
 *
 * @package Restyii\Client\Resource
 */
class Criteria extends \CComponent
{
    /**
     * @var string the search query
     */
    public $q;

    /**
     * @var array the filter parameters to send
     */
    public $filter = array();

    /**
     * @var int the number of result to return in one go
     */
    public $limit;

    /**
     * @var int the page number to request
     */
    public $page;

    /**
     * @var string|string[]|null the name(s) of the links to embed in the results
     */
    public $embed;


    /**
     * @var array the named scope configuration for the criteria
     */
    public $scopes = array();


    /**
     * Initializes and optionally configures the criteria.
     * @param array|null $config the configuration
     */
    public function __construct($config = null)
    {
        if ($config !== null) {
            if (is_string($config))
                $this->q = $config;
            else
                $this->fromArray($config);
        }
    }


    /**
     * Configure the criteria based on an array
     * @param array $config the config array
     */
    public function fromArray($config)
    {
        foreach($config as $key => $value){
            if ($key[0] === '_')
                $key = substr($key,1);
            $this->{$key} = $value;
        }
    }

    /**
     * Return an array representation of the criteria
     * @return array an array representation of the criteria
     */
    public function toArray()
    {
        $arr = array();
        if ($this->q !== null)
            $arr['q'] = (string) $this->q;
        if ($this->filter !== array())
            $arr['filter'] = $this->filter;
        if ($this->limit !== null)
            $arr['limit'] = (int) $this->limit;
        if ($this->page !== null)
            $arr['page'] = (int) $this->page;
        if ($this->embed !== null)
            $arr['_embed'] = is_array($this->embed) ? implode(',', $this->embed) : $this->embed;
        return $arr;
    }

    /**
     * Merge another criteria into this one
     *
     * @param Criteria $criteria the criteria to merge in
     *
     * @return $this the current criteria after merging
     */
    public function mergeWith(Criteria $criteria)
    {
        if ($criteria->q !== null) {
            if ($this->q === null)
                $this->q = $criteria->q;
            else
                $this->q .= ' '.$criteria->q;
        }
        if ($criteria->filter !== array()) {
            if ($this->filter === array())
                $this->filter = $criteria->filter;
            else
                $this->filter = \CMap::mergeArray($this->filter, $criteria->filter);
        }
        if ($criteria->scopes !== array()) {
            if ($this->scopes === array())
                $this->scopes = $criteria->scopes;
            else
                $this->scopes = $this->mergeItems($this->scopes, $criteria->scopes);
        }

        if ($criteria->limit !== null)
            $this->limit = $criteria->limit;
        if ($criteria->page !== null)
            $this->page = $criteria->page;
        if ($criteria->embed !== null) {
            if ($this->embed === null)
                $this->embed = $criteria->embed;
            else {
                if (!is_array($this->embed))
                    $this->embed = preg_split("/\s*,\s*/",$this->embed);
                if (is_array($criteria->embed))
                    $this->embed = $this->mergeItems($this->embed, $criteria->embed);
                else
                    $this->embed[] = $criteria->embed;
            }
        }
        return $this;
    }

    /**
     * Merge one or more arrays into the given target array
     *
     * @param array $target the target array
     * @param array $source,... the sources to merge into the target
     *
     * @return array the merged target
     */
    protected function mergeItems($target, $source)
    {
        $sources = func_get_args();
        array_shift($sources);

        foreach($sources as $source) {
            foreach($source as $key => $value) {
                if (is_numeric($key)) {
                    if (!in_array($value, $target))
                        $target[] = $value;
                }
                else if (isset($target[$key])) {
                    if (!is_array($target[$key]))
                        $target[$key] = array($target[$key]);
                    if (!is_array($value))
                        $value = array($value);
                    $target[$key] = $this->mergeItems($target[$key], $value);
                }
                else
                    $target[$key] = $value;
            }
        }

        return $target;
    }
}
