<?php

namespace Restyii\Model;

/**
 * Interface for Restyii data providers
 *
 * @property ModelInterface $model the model doing the searching
 *
 * @package Restyii\Model
 */
interface DataProviderInterface extends \IDataProvider
{

    /**
     * Gets the parameters for the data provider.
     * These are usually the parameters that were passed to the
     * model's `search()` method.
     *
     * @return array the parameters for the data provider
     */
    public function getParams();


    /**
     * Sets the parameters for the data provider.
     * @param array $params the parameters for the data provider.
     */
    public function setParams($params);

    /**
     * Gets the appropriate links for the data provider.
     *
     * @return array the appropriate links for the data provider
     */
    public function getLinks();
}
