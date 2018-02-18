<?php

namespace App\Support\Requests;

use Closure;
use InvalidArgumentException;

/**
 * trim             Trims a string
 * escape           Escapes HTML and special chars using php's filter_var
 * lowercase        Converts the given string to all lowercase
 * uppercase        Converts the given string to all uppercase
 * capitalize       Capitalize a string
 * cast             Casts a variable into the given type. Options are: integer, float, string, boolean, object, array and Laravel Collection.
 * date_format      Always takes two arguments, the date's given format and the target format, following DateTime notation.
 */
class Sanitizer
{
    /**
     *  Data to sanitize
     * @var array
     */
    protected $data;

    /**
     *  Filters to apply
     * @var array
     */
    protected $rules;

    /**
     *  Available filters as $name => $classPath
     * @var array
     */
    protected $filters = [
        'capitalize' => Filters\Capitalize::class,
        'cast' => Filters\Cast::class,
        'escape' => Filters\EscapeHTML::class,
        'format_date' => Filters\FormatDate::class,
        'lowercase' => Filters\Lowercase::class,
        'uppercase' => Filters\Uppercase::class,
        'trim' => Filters\Trim::class,
        'strip_tags' => Filters\StripTags::class,
    ];

    private $activateTagStrippingFilter = true;

    /**
     *  Create a new sanitizer instance.
     *
     * @param  array $data
     * @param  array $rules Rules to be applied to each data attribute
     * @param bool $activateTagStrippingFilter
     */
    public function __construct(array $data, array $rules, $activateTagStrippingFilter = true)
    {
        $this->data = $data;
        $this->rules = $this->parseRulesArray($rules);
        $this->activateTagStrippingFilter = $activateTagStrippingFilter;

    }

    /**
     *  Parse a rules array.
     *
     * @param  array $rules
     * @return array
     */
    protected function parseRulesArray(array $rules)
    {
        $parsedRules = [];
        foreach ($rules as $attribute => $attributeRules) {
            $attributeRulesArray = is_array($attributeRules) ? $attributeRules : explode('|', $attributeRules);
            foreach ($attributeRulesArray as $attributeRule) {
                $parsedRule = $this->parseRuleString($attributeRule);
                if ($parsedRule) {
                    $parsedRules[$attribute][] = $parsedRule;
                }
            }
        }
        return $parsedRules;
    }

    /**
     *  Parse a rule string formatted as filterName:option1, option2 into an array formatted as [name => filterName, options => [option1, option2]]
     *
     * @param  string $rule Formatted as 'filterName:option1, option2' or just 'filterName'
     * @return array           Formatted as [name => filterName, options => [option1, option2]]. Empty array if no filter name was found.
     */
    protected function parseRuleString($rule)
    {
        if (strpos($rule, ':') !== false) {
            list($name, $options) = explode(':', $rule, 2);
            $options = array_map('trim', explode(',', $options));
        } else {
            $name = $rule;
            $options = [];
        }
        if (!$name) {
            return [];
        }
        return compact('name', 'options');
    }

    /**
     *  Apply the given filter by its name
     * @param  $name
     * @param $value
     * @param array $options
     * @return \App\Filters\Filters
     */
    protected function applyFilter($name, $value, $options = [])
    {
        // If the filter does not exist, throw an Exception:
        if (!isset($this->filters[$name])) {
            throw new InvalidArgumentException("No filter found by the name of $name");
        }

        $filter = $this->filters[$name];
        if ($filter instanceof Closure) {
            return call_user_func_array($filter, [$value, $options]);
        } else {
            $filter = new $filter;
            return $filter->apply($value, $options);
        }
    }

    /**
     *  Sanitize the given data
     * @return array
     */
    public function sanitize()
    {
        $sanitized = [];
        foreach ($this->data as $name => $value) {
            $sanitized[$name] = $this->sanitizeAttribute($name, $value);
        }
        return $sanitized;
    }

    /**
     *  Sanitize the given attribute
     *
     * @param  string $attribute Attribute name
     * @param  mixed $value Attribute value
     * @return mixed   Sanitized value
     */
    protected function sanitizeAttribute($attribute, $value)
    {
        if (isset($this->rules[$attribute])) {
            foreach ($this->rules[$attribute] as $rule) {
                $value = $this->applyFilter($rule['name'], $value, $rule['options']);
            }
        }
        if ($this->activateTagStrippingFilter){
            $value = $this->applyFilter('strip_tags', $value);
        }
        return $value;
    }
}