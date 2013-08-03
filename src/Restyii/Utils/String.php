<?php

namespace Restyii\Utils;

/**
 * # String Utilities
 *
 * @author Charles Pick <charles@codemix.com>
 * @package Restyii\Utils
 */
class String
{
    /**
     * Convert a camel case string to dashes
     * @param string $input the input
     *
     * @return string the lower case, dasherized output
     */
    public static function camelCaseToDashes($input)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $input));
    }

    /**
     * Convert a dash separated string to camelCase
     * @param string $input the input
     *
     * @return string the camel case output
     */
    public static function dashesToCamelCase($input)
    {
        $replacer = function ($matches) {
            return strtoupper($matches[0][1]);
        };
        return preg_replace_callback('/-[a-zA-Z]/', $replacer, $input);
    }

    /**
     * Convert a camel case string to a more human readable format
     *
     * @param string $input the input
     *
     * @return string the humanized output
     */
    public static function humanize($input)
    {
        return ucwords(trim(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1 ', $input)));
    }

    /**
     * Converts a word to its plural form.
     * Note that this is for English only!
     * For example, 'apple' will become 'apples', and 'child' will become 'children'.
     * @param string $name the word to be pluralized
     * @return string the pluralized word
     */
    public static function pluralize($name)
    {
        $rules=array(
            '/(m)ove$/i' => '\1oves',
            '/(f)oot$/i' => '\1eet',
            '/(c)hild$/i' => '\1hildren',
            '/(h)uman$/i' => '\1umans',
            '/(m)an$/i' => '\1en',
            '/(s)taff$/i' => '\1taff',
            '/(t)ooth$/i' => '\1eeth',
            '/(p)erson$/i' => '\1eople',
            '/([m|l])ouse$/i' => '\1ice',
            '/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/(shea|lea|loa|thie)f$/i' => '\1ves',
            '/([ti])um$/i' => '\1a',
            '/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
            '/(bu)s$/i' => '\1ses',
            '/(ax|test)is$/i' => '\1es',
            '/s$/' => 's',
        );
        foreach($rules as $rule=>$replacement)
        {
            if(preg_match($rule,$name))
                return preg_replace($rule,$replacement,$name);
        }
        return $name.'s';
    }
}
