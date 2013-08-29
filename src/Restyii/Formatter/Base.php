<?php

namespace Restyii\Formatter;

/**
 * @method string text($value) {@see formatText}
 * @method string ntext($value) {@see formatNtext}
 * @method string html($value) {@see formatHtml}
 * @method string date($value) {@see formatDate}
 * @method string time($value) {@see formatTime}
 * @method string datetime($value) {@see formatDatetime}
 * @method string boolean($value) {@see formatBoolean}
 * @method string email($value) {@see formatEmail}
 * @method string image($value) {@see formatImage}
 * @method string url($value) {@see formatUrl}
 * @method string number($value) {@see formatNumber}
 * @method string size($value) {@see formatSize}
 *
 * @method string choice($value) {@see formatChoice}
 * @method string attribute($model, $attribute) {@see formatAttribute}
 */
class Base extends \CFormatter
{
    /**
     * Format a choice from a list of labels..
     *
     *
     * @param string $choice the selection
     * @param array $options the options
     *
     * @return string the formatted choice
     */
    public function formatChoice($choice, $options = array())
    {
        if (isset($options[$choice]))
            return $options[$choice];
        else
            return $choice;
    }

    /**
     * @param \Restyii\Model\ActiveRecord $model the model instance
     * @param string $attribute the attribute name
     *
     * @return string
     */
    public function formatAttribute($model, $attribute)
    {
        $format = $model->getAttributeFormat($attribute);
        if ($format == 'choice' && method_exists($model, $attribute.'Labels')) {
            $methodName = $attribute.'Labels';
            return $this->formatChoice($model->{$attribute}, $model->{$methodName}());
        }
        $methodName = 'format'.$format;
        if (method_exists($this, $methodName))
            return $this->{$methodName}($model->{$attribute});
        else
            return $this->formatText($model->{$attribute});
    }

    /**
     * Format a password.
     * Always returns asterisks, regardless of length
     *
     * @param string $value
     *
     * @return string the password
     */
    public function formatPassword($value)
    {
        return '************************************';
    }

    /**
     * Check the length of a string. If it's too long truncate it and append "..." chars
     *
     * If $soft is true, the string will be truncated at the last space before $length.
     * In this case the returned string can be shorter than $length.
     *
     * @param string $text string to truncate
     * @param integer $length max length of this string. Default ist 100.
     * @param string $append (optional) what to append to the truncated string
     * @param bool $soft (optional) whether to truncate at spaces only. Defaults to false.
     * @return string the truncated string
     */
    public function formatShortText($text, $length=100, $append='...', $soft=false)
    {
        if(mb_strlen($text) > $length)
        {
            if($soft && ($pos=mb_strpos($text,' ',$length))!==false) {
                $length=$pos;
            }
            $text=mb_substr($text,0,$length);
            return $text.$append;
        }

        return $text;
    }

}
