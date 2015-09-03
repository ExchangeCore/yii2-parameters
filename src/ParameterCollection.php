<?php
namespace exchangecore\yii2\parameters;

use Yii;
use yii\db\ActiveQuery;

class ParameterCollection
{

    /** @var Parameter[] */
    protected $parameters = [];
    protected $errors = [];

    /**
     * @param Parameter $parameter
     */
    public function addParameter($parameter)
    {
        $this->parameters[$parameter->getKey()] = $parameter;
    }

    /**
     * @param string $parameterKey
     * @return Parameter|null
     */
    public function getParameter($parameterKey)
    {
        return isset($this->parameters[$parameterKey]) ? $this->parameters[$parameterKey] : null;
    }

    public function validateInput()
    {
        foreach($this->parameters as $parameter) {
            if (!$parameter->isValidComparison()) {
                $this->addError(
                    Yii::t(
                        'modules/parameters',
                        '{comparisonType} is not a valid comparison type for {parameterDisplayName}',
                        [
                            'comparisonType' => Comparison::getComparisonLabel($parameter->getComparison()),
                            'parameterDisplayName' => $parameter->getDisplayName()
                        ]
                    )
                );
            }

            if (!$parameter->isRequiredFulfilled()) {
                $this->addError(
                    Yii::t(
                        'modules/parameters',
                        '{parameterDisplayName} is required',
                        [
                            'parameterDisplayName' => $parameter->getDisplayName()
                        ]
                    )
                );
            } elseif (!$parameter->isValidValue()) {
                $this->addError(
                    Yii::t(
                        'modules/parameters',
                        '{value} is not a valid value for {parameterDisplayName}',
                        [
                            'value' => implode(',', $parameter->getFormattedValue()),
                            'parameterDisplayName' => $parameter->getDisplayName()
                        ]
                    )
                );
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @param ActiveQuery $queryBuilder
     * @return ActiveQuery
     */
    public function attachQueryBuilder(&$queryBuilder)
    {
        foreach ($this->parameters as $parameter) {
            if($parameter->getDatabaseFilterField() !== null && $parameter->getHasInput()) {
                $this->attachParameterToQueryBuilder($queryBuilder, $parameter);
            }
        }
        return $queryBuilder;
    }

    /**
     * @param array $data
     */
    public function afterRetrievalProcess(&$data)
    {
        $filterParameters = [];
        foreach($this->parameters as $param) {
            if ($param->getHasInput() && $param->getAfterDataFilter() instanceof \Closure) {
                $filterParameters[] = $param;
            }
        }

        if(!empty($filterParameters)) {
            foreach ($data AS $key => $row) {
                foreach ($filterParameters as $param) {
                    /** @var Parameter $param*/
                    $filter = $param->getAfterDataFilter();
                    $removeRow = !$filter($row, $param);
                    if($removeRow) {
                        unset($data[$key]);
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param ActiveQuery $queryBuilder
     * @param Parameter $parameter
     */
    protected function attachParameterToQueryBuilder(&$queryBuilder, $parameter)
    {
        switch($parameter->getComparison()) {
            case Comparison::AFTER:
            case Comparison::GREATER_THAN:
                $queryBuilder->andWhere(
                    [
                        '>',
                        $parameter->getDatabaseFilterField(),
                        $parameter->getDatabaseFilterValue()[0]
                    ]
                );
                break;
            case Comparison::BEFORE:
            case Comparison::LESS_THAN:
                $queryBuilder->andWhere(
                    [
                        '<',
                        $parameter->getDatabaseFilterField(),
                        $parameter->getDatabaseFilterValue()[0]
                    ]
                );
                break;
            case Comparison::BETWEEN:
                $queryBuilder->andWhere(
                    [
                        'between',
                        $parameter->getDatabaseFilterField(),
                        $parameter->getDatabaseFilterValue()[0],
                        $parameter->getDatabaseFilterValue()[1]
                    ]
                );
                break;
            case Comparison::CONTAINS:
                $queryBuilder->andWhere(
                    [
                        'like',
                        $parameter->getDatabaseFilterField(),
                        $parameter->getDatabaseFilterValue()[0]
                    ]
                );
                break;
            case Comparison::STARTS_WITH:
                $queryBuilder->andWhere(
                    [
                        'like',
                        $parameter->getDatabaseFilterField(),
                        $parameter->getDatabaseFilterValue()[0]. '%',
                        false
                    ]
                );
                break;
            case Comparison::ENDS_WITH:
                $queryBuilder->andWhere(
                    [
                        'like',
                        $parameter->getDatabaseFilterField(),
                        '%' . $parameter->getDatabaseFilterValue()[0],
                        false
                    ]
                );
                break;
            case Comparison::NULL:
                $queryBuilder->andWhere([$parameter->getDatabaseFilterField() => null]);
                break;
            case Comparison::NOT_NULL:
                $queryBuilder->andWhere(['not', [$parameter->getDatabaseFilterField() => null]]);
                break;
            case Comparison::EQUALS:
                $queryBuilder->andWhere(
                    [$parameter->getDatabaseFilterField() => $parameter->getDatabaseFilterValue()[0]]
                );
                break;
            case Comparison::NOT_EQUALS:
                $queryBuilder->andWhere(
                    ['not', [$parameter->getDatabaseFilterField() => $parameter->getDatabaseFilterValue()[0]]]
                );
                break;
            case Comparison::ONE_OF:
                $queryBuilder->andWhere(['in', $parameter->getDatabaseFilterField(), $parameter->getDatabaseFilterValue()]);
                break;
        }
    }

    public function addError($errorMessage)
    {
        $this->errors[] = $errorMessage;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }
} 