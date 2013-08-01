<?php
/**
 * The following variables are available in this template:
 * @var CrudCode $this object
 */
$modelClass = $this->getModelClass();
$model = new $modelClass; /* @var \Restyii\Model\ActiveRecord $model */
$model->setScenario('create');
?>
<?="<?php"?>
/**
 * Displays the <?=$model->classLabel()?> in a list
 *
 * @partial
 * @var <?=$modelClass?> $data the <?=$model->classLabel()?> being displayed
 * @var <?=$this->getControllerClass()?> $this the controller rendering the view
 * @var Formatter $format the content formatter
 */
?>
<a href="<?="<?="?>$data->createUrl()?>" class="list-group-item clearfix" itemscope="<?="<?="?>$data->createUrl()?>" itemtype="<?='<?='.$modelClass?>::model()->createUrl("schema")?>">
    <h4 class="list-group-item-heading">
        <span itemprop="instanceLabel"><?="<?="?>$format->text($data->instanceLabel()?></span>
    </h4>
</a>
