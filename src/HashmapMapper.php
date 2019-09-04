<?php

namespace Apantle\HashMapper;

class HashmapMapper implements HashmapMapperInterface
{
    /**
     * @var mixed associative array of sourceKeys (strings) to rules
     */
    protected $rules;

    /**
     * @var array that will store mapped keys from source
     */
    protected $mapped;
    /**
     * @var string with the name of matched key in source hashmap
     */
    protected $sourceKeyMatched;

    /**
     * @var bool allows omit the spread ... special target key to spread returned data in target
     */
    protected $implicitSpread = false;

    /**
     * @var mixed allows pass not explicit named keys in rules to target object
     */
    protected $passNotMatchedKeys = false;

    /**
     * @var array functions detected as unary to run
     */
    protected $unaryFunctionsRegistry = [];

    public function __construct($rules, $options = [])
    {
        $this->rules = $rules;
        $this->processOptions($options);
    }

    protected function processOptions($options)
    {
        if (empty($options)) {
            return;
        }
        if (isset($options['implicitSpread']) && $options['implicitSpread']) {
            $this->implicitSpread = true;
        }
        if (isset($options['passNotMatchedKeys']) && $options['passNotMatchedKeys']) {
            $this->passNotMatchedKeys = $options['passNotMatchedKeys'];
        }
    }

    public function apply($hashmap, $sourceContext = null)
    {
        $this->mapped = [];
        $this->maybePassToTargetUnmatchedKeys($hashmap);

        array_walk($this->rules, function ($rule, $key, $hashmap) use ($sourceContext) {
            if (array_key_exists($key, $hashmap)) {
                $this->sourceKeyMatched = $key;
                $this->applyRule($rule, $hashmap, $sourceContext);
            }
        }, $hashmap);
        return $this->mapped;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'apply'], func_get_args());
    }

    protected function applyRule($rule, $hashmap, $sourceContext = null)
    {
        if (is_array($rule)) {
            $this->applyConsRule($rule[0], $rule[1], $hashmap, $sourceContext);
        }
        if ($this->implicitSpread) {
            $this->maybeImplicitSpreadCall($rule, $hashmap, $sourceContext);
        }
        if (is_string($rule)) {
            $this->mapped[$rule] = $hashmap[$this->sourceKeyMatched];
        }
    }

    protected function maybeImplicitSpreadCall($rule, $hashmap, $sourceContext = null)
    {
        if (is_callable($rule) || $rule instanceof HashmapMapperInterface) {
            $this->applyConsRule('...', $rule, $hashmap, $sourceContext);
        }
    }

    protected function applyConsRule($targetKey, $actualRule, $hashmap, $sourceContext)
    {
        if (!is_string($targetKey)) {
            return;
        }
        if (is_null($sourceContext)) {
            $sourceContext = $hashmap;
        }
        $hashmapValueAtKey = $hashmap[$this->sourceKeyMatched];

        $actualMapperRuleResult = $this->doCallActualMapperRule($actualRule, $hashmapValueAtKey, $sourceContext);

        $this->receiveDataReturnedByRule($targetKey, $actualMapperRuleResult);
    }

    protected function receiveDataReturnedByRule($targetKey, $returnValue)
    {
        if ($targetKey === '...') {
            foreach ($returnValue as $targetKey => $value) {
                $this->mapped[$targetKey] = $value;
            }
            return;
        }
        $this->mapped[$targetKey] = $returnValue;
    }

    protected function doCallActualMapperRule($actualRule, $hashmapValueAtKey, $sourceContext = null)
    {
        if (is_callable($actualRule)) {
            $this->checkUnarySuportRequired($actualRule);
            return $this->doCallUnarySupportProtection($actualRule, $hashmapValueAtKey, $sourceContext);
        }
        if ($actualRule instanceof HashmapMapperInterface) {
            return $actualRule->apply($hashmapValueAtKey, $sourceContext);
        }
        throw new \LogicException('mapper is not a callable or instance of HashmapMapperInterface');
    }

    protected function checkUnarySuportRequired($actualRule)
    {
        if (!is_string($actualRule)) {
	        return;
	    }
        if (isset($this->unaryFunctionsRegistry[$actualRule])) {
            return;
        }
        $reflector = new \ReflectionFunction($actualRule);
        $this->unaryFunctionsRegistry[$actualRule] = boolval($reflector->getNumberOfParameters() === 1);
    }

    protected function doCallUnarySupportProtection($actualRule, $hashmapValueAtKey, $sourceContext)
    {
        if (is_string($actualRule) && $this->unaryFunctionsRegistry[$actualRule]) {
            return call_user_func($actualRule, $hashmapValueAtKey);
        }
        return call_user_func($actualRule, $hashmapValueAtKey, $sourceContext);
    }

    protected function maybePassToTargetUnmatchedKeys($hashmap)
    {
        if (!$this->passNotMatchedKeys) {
            return;
        }
        array_walk($hashmap, function ($value, $key, $ruleKeys) {
            if (in_array($key, $ruleKeys)) {
                return;
            }
            if (is_bool($this->passNotMatchedKeys)) {
                $this->mapped[$key] = $value;
            } else {
                $mappedValueThrough = $this->doCallActualMapperRule($this->passNotMatchedKeys, $value);
                $this->mapped[$key] = $mappedValueThrough;
            }
        }, array_keys($this->rules));
    }

    public function getCollectionMapper()
    {
        return new self([], [
            'passNotMatchedKeys' => $this,
        ]);
    }
}
