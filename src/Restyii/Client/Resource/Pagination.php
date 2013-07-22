<?php

namespace Restyii\Client\Resource;

use CDbCriteria;

class Pagination extends \CPagination
{
    /**
     * @param Criteria $criteria
     */
    public function applyLimit($criteria)
    {
        $criteria->page = $this->getCurrentPage();
        $criteria->limit = $this->getPageSize();
    }

}
