<?php
namespace Apantle\HashMapper;

function hashMapper($rules, $options = []): HashmapMapper
{
    return new HashmapMapper($rules, $options);
}

function collection(HashmapMapperInterface $hashMapper): HashmapMapper
{
    return $hashMapper->getCollectionMapper();
}
