<?php
namespace exchangecore\yii2\parameters;

use yii\base\Object;

class Parameter extends Object
{

    protected $required = false;
    protected $initialize = false;
    protected $displayFormatter;
    protected $typeHandle = 'text';
    protected $availableComparisons = null;
    protected $comparison = Comparison::EQUALS;
    protected $value;
    protected $valueOptions;
    protected $key;
    protected $databaseFilterField;
    /** @var null|\Closure|array */
    protected $databaseFilterValue;
    protected $displayName;
    protected $modifiable = true;
    protected $afterDataFilter = true;
    protected $hasInput = false;

    /**
     * @return \StdClass An object to be encoded for the Parameter widget js library
     */
    public function getJsObject()
    {
        $obj = new \StdClass();
        $obj->key = $this->getKey();
        $obj->displayName = $this->getDisplayName();
        $obj->initialize = $this->getInitialize();
        $obj->value = $this->getFormattedValue();
        $obj->valueOptions = $this->getValueOptions();
        $obj->required = $this->getIsRequired();
        $obj->type = $this->getTypeHandle();
        $obj->comparisons = $this->getAvailableComparisons();
        $obj->comparison = $this->getComparison();
        $obj->modifiable = $this->getIsModifiable();

        return $obj;
    }

    /**
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function setIsRequired($required)
    {
        $this->required = $required ? true : false;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInitialize()
    {
        return $this->initialize;
    }

    /**
     * @param bool $initialize
     * @return $this
     */
    public function setInitialize($initialize)
    {
        $this->initialize = $initialize ? true : false;
        return $this;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        if(!is_array($this->value)) {
            $arr[] = $this->value;
            $this->value = $arr;
        }
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName !== null ? $this->displayName : $this->key;
    }

    /**
     * @param string $displayName A translated display name
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayFormatter()
    {

        if($this->displayFormatter === null) {
            if(isset(Type::getTypeList()[$this->getTypeHandle()]['formatter'])) {
                return Type::getTypeList()[$this->getTypeHandle()]['formatter'];
            }

            return 'raw';
        }

        return $this->displayFormatter;
    }

    /**
     * @param string|array $displayFormatter See yii\i18n\Formatter
     * @return $this
     */
    public function setDisplayFormatter($displayFormatter)
    {
        $this->displayFormatter = $displayFormatter;
        return $this;
    }

    public function getFormattedValue()
    {
        $values = [];
        foreach($this->getValue() as $value) {
            $value = $value === null ? '' : $value;
            $values[] = \Yii::$app->formatter->format($value, $this->getDisplayFormatter());
        }

        return $values;
    }

    /**
     * @return string
     */
    public function getTypeHandle()
    {
        return $this->typeHandle;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setTypeHandle($type)
    {
        $this->typeHandle = $type;
        return $this;
    }

    /**
     * @return int|null When an integer this will be used in place of the type default to display a list of comparisons
     */
    public function getAvailableComparisons()
    {
        return $this->availableComparisons;
    }

    /**
     * @param int|null $availableComparisons
     * @return $this
     */
    public function setAvailableComparisons($availableComparisons)
    {
        $this->availableComparisons = $availableComparisons;
        return $this;
    }

    /**
     * @return int
     */
    public function getComparison()
    {
        return $this->comparison;
    }

    /**
     * @param $comparison
     * @return $this
     */
    public function setComparison($comparison)
    {
        $this->comparison = $comparison;
        return $this;
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * @param array $valueOptions
     * @return $this
     */
    public function setValueOptions($valueOptions)
    {
        $this->valueOptions = $valueOptions;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsModifiable()
    {
        return $this->modifiable;
    }

    /**
     * @param boolean $modifiable
     * @return $this
     */
    public function setIsModifiable($modifiable)
    {
        $this->modifiable = $modifiable ? true : false;
        return $this;
    }

    /**
     * @return boolean|\Closure
     */
    public function getAfterDataFilter()
    {
        return $this->afterDataFilter;
    }

    /**
     * This function gets run on all data that is returned to determine if the record should be kept or thrown away. A
     * closure which takes in a row/active record object as the input and returns true if the row should be kept or false
     * if the record should be discarded, for example:
     * <code>
     * function($input) {
     *      return $input['timestamp'] < strtotime('now');
     * }
     * </code>
     * @param boolean|\Closure $afterDataFilter
     * @return $this
     */
    public function setAfterDataFilter($afterDataFilter)
    {
        $this->afterDataFilter = $afterDataFilter;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasInput()
    {
        return $this->hasInput;
    }

    /**
     * @param boolean $hasInput
     */
    public function setHasInput($hasInput)
    {
        $this->hasInput = $hasInput ? true : false;
    }

    /**
     * This function takes in an array of input (usually from a POST or GET request) in the format of
     * <pre>
     * [
     *      'param0' => [
     *          'key' => 'parameterKey',
     *          'comparison' => 1,
     *          'values' => [1] //this will always be an array of values, even if there is only one value
     *      ]
     * ]
     * </pre>
     * @param array $input
     * @return $this
     */
    public function parseFromInput($input)
    {
        foreach ($input as $key => $var) {
            if(strpos($key, 'param') === 0) {
                if($var['key'] == $this->getKey()) {
                    $this->setValue($var['values']);
                    $this->setComparison((int) $var['comparison']);
                    $this->setHasInput(true);
                    break;
                }
            }
        }
        return $this;
    }

    public function isValidComparison()
    {
        $comparisons = $this->getAvailableComparisons();
        if ($comparisons === null) {
            $comparisons = Type::getTypeList()[$this->getTypeHandle()]['comparisons'];
        }

        return ($this->getComparison() & $comparisons) == $this->getComparison();
    }

    public function isRequiredFulfilled()
    {
        if(!$this->getIsRequired()) {
            return true;
        }

        $comparisonValueType = Comparison::getComparisonValueType($this->getComparison());
        if(Comparison::VALUE_NONE == $comparisonValueType) {
            $notEmpty = true;
        } elseif (Comparison::VALUE_NORMAL == $comparisonValueType) {
            $notEmpty = isset($this->getValue()[0]) && $this->getValue()[0] !== null && $this->getValue()[0] !== '';
        } elseif (Comparison::VALUE_DOUBLE == $comparisonValueType) {
            $notEmpty = isset($this->getValue()[0]) && $this->getValue()[0] !== null && $this->getValue()[0] !== ''
                && isset($this->getValue()[1]) && $this->getValue()[1] !== null && $this->getValue()[1] !== '';
        } elseif (Comparison::VALUE_MULTIPLE == $comparisonValueType) {
            $notEmpty = !empty($this->getValue());
        } else {
            $notEmpty = true;
        }

        return ($notEmpty && $this->getHasInput());
    }

    public function isValidValue()
    {
        if (!$this->isRequiredFulfilled()) {
            return false;
        }

        if($this->getValueOptions() !== null) {
            foreach($this->getValue() as $val) {
                if (!in_array($val, $this->getValueOptions())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getDatabaseFilterField()
    {
        return $this->databaseFilterField;
    }

    /**
     * @param string $databaseFilterField
     * @return $this
     */
    public function setDatabaseFilterField($databaseFilterField)
    {
        $this->databaseFilterField = $databaseFilterField;
        return $this;
    }

    /**
     * @return array
     */
    public function getDatabaseFilterValue()
    {
        if($this->databaseFilterValue === null) {
            return $this->getValue();
        } elseif ($this->databaseFilterValue instanceof \Closure) {
            return $this->databaseFilterValue->__invoke($this);
        }
        return $this->databaseFilterValue;
    }

    /**
     * @param mixed $databaseFilterValue
     * @return $this
     */
    public function setDatabaseFilterValue($databaseFilterValue)
    {
        $this->databaseFilterValue = $databaseFilterValue;
        return $this;
    }

} 