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
            'guzzle' => array(
                'class' => 'Restyii\Action\Root\Guzzle',
            )
        );
    }
}
