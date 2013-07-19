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
 * # <?=$modelClass?> Active Record.
 * This is the active record class for the "<?php echo $tableName; ?>" table.
 * @package <?=$this->modelPath."\n"?>
 *
<?php foreach($columns as $column): ?>
 * @property <?php echo $types[$column->name].' $'.$column->name.(isset($descriptions[$column->name]) ? ' '.$descriptions[$column->name] : '')."\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
<?php foreach($relations as $name=>$relation): ?>
 * @property <?php
    if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
    {
        $relationType = $matches[1];
        $relationModel = $matches[2];

        switch($relationType){
            case 'HAS_ONE':
                echo $relationModel.' $'.$name."\n";
            break;
            case 'BELONGS_TO':
                echo $relationModel.' $'.$name."\n";
            break;
            case 'HAS_MANY':
                echo $relationModel.'[] $'.$name."\n";
            break;
            case 'MANY_MANY':
                echo $relationModel.'[] $'.$name."\n";
            break;
            default:
                echo 'mixed $'.$name."\n";
        }
    }
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

    /**
     * @inheritDoc
     */
    public function tableName()
    {
        return '<?php echo $tableName; ?>';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array(
<?php foreach($rules as $rule): ?>
            <?php echo $rule.",\n"; ?>
<?php endforeach; ?>
            // @todo Remove the attributes that should not be searched.
            array('<?php echo implode(', ', array_keys($columns)); ?>', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @inheritDoc
     */
    public function relations()
    {
        // @todo verify these auto generated relation names
        return array(
<?php foreach($relations as $name=>$relation): ?>
            <?php echo "'$name' => $relation,\n"; ?>
<?php endforeach; ?>
        );
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array(
<?php foreach($labels as $name=>$label): ?>
            <?php echo "'$name' => Yii::t('$urlName', \"$label\"),\n"; ?>
<?php endforeach; ?>
        );
    }

    /**
     * @inheritDoc
     */
    public function attributeDescriptions()
    {
        return array(
<?php foreach($descriptions as $name=>$description): ?>
            <?php echo "'$name' => Yii::t('$urlName', \"$description\"),\n"; ?>
<?php endforeach; ?>
        );
    }

<?php if($connectionId!='db'):?>
    /**
     * @return CDbConnection the database connection used for this class
     */
    public function getDbConnection()
    {
        return Yii::app()-><?php echo $connectionId ?>;
    }

<?php endif?>
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
