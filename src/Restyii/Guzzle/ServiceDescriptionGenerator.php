<?php

namespace Restyii\Guzzle;
use Guzzle\Service\Description\ServiceDescription;
use Restyii\Meta\Schema;

/**
 * Generates service descriptions for Guzzle
 * @author Charles Pick <charles@codemix.com>
 * @package Restyii\Guzzle
 */
class ServiceDescriptionGenerator extends \CComponent
{
   public function generate(Schema $schema)
   {
       $config = array(
           'name' => $schema->getModuleSchema()
       );
       $description = new ServiceDescription($config);

       return $description;
   }
}
