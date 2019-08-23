<?php
namespace Apantle\HashMapper;

function identity($item)
{
    return $item;
}

function compose(callable $f, callable $g): callable
{
    return function () use ($f, $g) {
        $fun_args = func_get_args();
        return $f(call_user_func_array($g, $fun_args));
    };
}

function head(array $items)
{
    return $items[0];
}

function hashMapper($rules, $options = []): HashmapMapper
{
    return new HashmapMapper($rules, $options);
}

function constant($value): callable
{
    return function () use ($value) {
        return $value;
    };
}

function collection(HashmapMapperInterface $hashMapper): HashmapMapperInterface
{
    return $hashMapper->getCollectionMapper();
}
