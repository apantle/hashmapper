[![Build Status](https://travis-ci.org/apantle/hashmapper.svg?branch=master)](https://travis-ci.org/apantle/hashmapper)  [![Maintainability](https://api.codeclimate.com/v1/badges/5fa613bc0b87e5975a6a/maintainability)](https://codeclimate.com/github/apantle/hashmapper/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/5fa613bc0b87e5975a6a/test_coverage)](https://codeclimate.com/github/apantle/hashmapper/test_coverage)

# Objective

This is a minimalistic library aimed to reuse logic
on HashTables mapping, that i use over and over i.e.
consuming API results to pass to Twig views.

Makes easy to do filtering/renaming of unwanted keys through simple
dictionary of keys of source to target. Through callbacks supports
any transformation, and passing it another callable or another instance
of HashMapper, pretty complex transformation of associative arrays,
a.k.a as Hashmaps for friends.

```php
use function Apantle\HashMapper\hashMapper;

$target = hashMapper($specs, $options)($source);
```

## Installation

You can install the package via composer:

```bash
composer require apantle/hashmapper
```

## Simple key mapping

Change one key in input, only output that in target.

|Input|Mapper|Output|
|:---|:---:|---:|
|```['origin' => 'Africa'];```|```hashMapper(['origin' => 'roots]) ```|```['roots' => 'Africa']```|

## Callback key mapping

For somewhat complex transforms, you can use a function
that will receive as arguments:
- the value of the source hashmap specified at key:
- the whole hashmap if you need other values of it

```php
assert(hashMapper([
    'place' => 'Caso CIDH',
    'date' => [
        'fecha',
        function($date, $source) {
            extract($date);
            $date = date_create_from_format('Y/m/d', "{$year}/{$month}/{$day}");
            return $date->format('Y-m-d');
        }
    ],
])([
    'date' => [
        'year' => 2006,
        'month' => 5,
        'day' => 4,
    ],
    'place' => 'San Salvador Atenco',
]) === [
    'Caso CIDH' => 'San Salvador Atenco',
    'fecha' => '2006-05-04'
]);
```

## Use another HashMapper 

If you have a complex subkey that is not easily mapped with a simple function,
you could use another HashMapper with the spec for that subkey, as the mapper
for that key.

```php
assert(hashMapper([
    'sourceKey' => hashMapper([ 'value' => 'legend' ])
])([
    'sourceKey' => [
        'value' => 'to pass to HashMapper',
        'ignored' => 'it should not appear'
    ],
]) === [
    'sourceKey => [
        'legend' => 'to pass to HashMapper'
    ]
]);
```

## Spread Operator Mapping with Callable

If you want to take a key with subkeys of the source and _spread it_ (copy
the dictionary of key and values it contains) on the target hashmap, you
can pass a tuple with the string `'...'` as the target key, and your chosen callable.

```php
assert(hashMapper([
    'wp:term' => ['...', 'Apantle\FunPHP\identity']
])([
    'wp:term' => [
         'id' => 31925,
         'link' => 'http://example.com/category/test-term/',
         'name' => 'Test term',
         'slug' => 'test-term',
         'taxonomy' => 'category',
    ],
    'ignored' => 'right'
]) === [
     'id' => 31925,
     'link' => 'http://example.com/category/test-term/',
     'name' => 'Test term',
     'slug' => 'test-term',
     'taxonomy' => 'category',
]);
```

## Implicit spread (not specifying '...' key)

If you need all the dictionaries inside top level keys be spread into the
target, rather than writing your mapping spec as a tuple, you can give it
only a callable, specifying the option `implicitSpread => true` in the
constructor to the Mapper functor.

```php
assert(hashMaper(
    [
        'wp:term' => compose('Apantle\FunPHP\head', 'Apantle\FunPHP\identity'),
    ],
    [
        'implicitSpread' => true
    ]
)([
    'wp:term' => [
        [
            'id' => 31925,
            'link' => 'http://example.com/category/test-term/',
            'name' => 'Test term',
            'slug' => 'test-term',
            'taxonomy' => 'category',
        ]
    ],
]) === [
    'id' => 31925,
    'link' => 'http://example.com/category/test-term/',
    'name' => 'Test term',
    'slug' => 'test-term',
    'taxonomy' => 'category',
]);
 ``` 

### Call a HashMapper as Functor object

For better reuse, now offers through the `__invoke` magic, a simpler way to use
it to map a collection of associative arrays, as `array_map`, `array_reduce` or
`Collection::map` (from `Illuminate\Support`). 

### Reuse a HashMapper to transform an array of associative arrays

Instead of using the HashMapper as the function for `array_map` or `Collection::map`,
you can use our own helper, that applies the same set of transformations
to every array passed.

```php
$collectionTransformed = collection(hashMapper($specs))($arrayOfAssociativeArrays);
```

See [issue:1](https://github.com/apantle/hashmapper/issues/1) for more complete examples in test sources.
