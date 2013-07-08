# Restyii

A RESTful HATEOAS compliant extension for Yii. Allows your application to serve and accept json, jsonp, xml, csv or html using the same code.


# Installation.

Install using composer. Requires php 5.3 or later.

# Configuration.

Restyii requires a number of custom application components to be configured. Add the following to your application config:

    'components' => array(
      'urlManager' => array(
        'class' => 'Restyii\Web\UrlManager`,
      ),
      'request' => array(
        'class' => 'Restyii\Web\Request`,
      ),

      // ...
    )
