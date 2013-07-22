<?php
/**
 * This is the template for generating the model class of a specified table.
 * - $this: the ResourceCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 */
?>
<?php echo "<?php\n"; ?>

/**
 * # <?=$modelClass?> Resource.
 * This is the resource class for the "<?php echo $urlName; ?>" REST end point.
 * @package <?=$this->modelPath."\n"?>
 *
<?php foreach($columns as $column): ?>
 * @property <?php echo $types[$column->name].' $'.$column->name.(isset($descriptions[$column->name]) ? ' '.$descriptions[$column->name] : '')."\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
<?php foreach($links as $name=>$link): ?>
<?php
    if (preg_match("~^array\(('|\")(\w+)('|\")\)$~", $link['profile'], $matches))
    {
        $relationModel = $matches[2];
        echo ' * @property '.$relationModel.'[] $'.$name."\n";
    }
    else if (preg_match("/('|\")(\w+)('|\")/", $link['profile'], $matches))
        echo ' * @property '.$matches[2].' $'.$name."\n";

    ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
<?php if (count($constants)): foreach($constants as $columnName => $consts): ?>

<?php foreach($consts as $name => $val): ?>
    const <?=$name?> = "<?=$val?>";
<?php endforeach; endforeach; endif;?>
<?php if (count($constants)): foreach($constants as $columnName => $consts): ?>

    /**
     * Declares the labels for the `<?=$columnName?>` options.
     * @return array the labels, 'value' => 'Label'
     */
    public function <?=$columnName?>Labels()
    {
        return array(
<?php foreach($consts as $name => $val): ?>
            self::<?=$name?> => Yii::t('<?=$urlName?>', "<?=$this->generateAttributeLabel($val)?>"),
<?php endforeach; ?>
        );
    }
<?php endforeach; endif;?>

    /**
     * @inheritDoc
     */
    public function instanceLabel()
    {
<?php if (is_array($labelAttribute)): ?>
        return Yii::t(
            '<?=$urlName?>',
            "<?=implode(" ", array_map(function($attr){ return '{'.$attr.'}';}, $labelAttributes))?>",
            array(
<?php foreach($labelAttribute as $attr): ?>
                '{<?=$attr?>}' => $this-><?=$attr?>,
<?php endforeach ?>
        ));
<?php else: ?>
        return $this-><?=$labelAttribute?>;
<?php endif; ?>
    }

    /**
     * @inheritDoc
     */
    public function classLabel($plural = false)
    {
        if ($plural)
            return Yii::t('<?=$urlName?>', "<?=$this->pluralize($this->generateAttributeLabel($modelClass))?>");
        else
            return Yii::t('<?=$urlName?>', "<?=$this->generateAttributeLabel($modelClass)?>");
    }

    /**
     * @inheritDoc
     */
    public function resourceName()
    {
        return '<?=$urlName?>';
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return <?php echo $modelClass; ?> the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
