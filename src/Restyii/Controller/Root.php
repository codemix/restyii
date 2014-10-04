<?php

namespace Restyii\Controller;

abstract class Root extends Base
{
    /**
     * @inheritDoc
     */
    public function actions()
    {
        return array(
            'index' => array(
                'class' => 'Restyii\Action\Root\Index',
            ),
            'trace' => array(
                'class' => 'Restyii\Action\Generic\Trace',
            ),
            'error' => array(
                'class' => 'Restyii\Action\Root\Error',
            )
        );
    }
}
