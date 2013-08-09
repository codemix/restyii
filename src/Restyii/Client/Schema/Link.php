<?php


namespace Restyii\Client\Schema;

use Restyii\Client\Resource\Model;

class Link extends AbstractEntity
{
    /**
     * @var string the link name
     */
    public $name;

    /**
     * @var string the link title
     */
    public $title;

    /**
     * @var string the link URL
     */
    public $href;

    /**
     * @var Model the owner of the link
     */
    public $owner;

    /**
     * @var bool true if this is a templated link
     */
    public $templated = false;

    /**
     * @var string the link profile. When set this refers to the entity name (by default - model class) that the link is for.
     */
    public $profile;

}
