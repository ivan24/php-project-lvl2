<?php

namespace Differ;

use function Functional\map;

const REMOVE = 'remove';
const ADD = 'add';
const UNCHANGED = 'unchanged';
const CHANGED = 'changed';

function genDiff(string $pathToFile1, string $pathToFile2, string $format): string
{
    $first = getContent($pathToFile1);
    $second = getContent($pathToFile2);

    $diff = map($first, function ($item, $key) use ($second) {
        if (!array_key_exists($key, $second)) {
            return ['type' => REMOVE, 'key' => $key, 'value' => $item];
        }
        if ($second[$key] === $item) {
            return ['type' => UNCHANGED, 'key' => $key, 'value' => $item];
        }

        if ($second[$key] !== $item) {
            return ['type' => CHANGED, 'key' => $key, 'value' => $item, 'value2' => $second[$key]];
        }
    });

    map($second, function ($item, $key) use ($first, &$diff) {
        if (!array_key_exists($key, $first)) {
            $diff[$key] = ['type' => ADD, 'key' => $key, 'value' => $item];
        }
    });

    ksort($diff);

    return diffToStr($diff);
}

function diffToStr(array $diff): string
{
    $items = array_map(function ($item) {
        return match ($item['type']) {
            ADD => sprintf('+ %s: %s', $item['key'], scalarToStr($item['value'])),
            UNCHANGED => sprintf('  %s: %s', $item['key'], scalarToStr($item['value'])),
            REMOVE => sprintf('- %s: %s', $item['key'], scalarToStr($item['value'])),
            CHANGED => sprintf(
                "- %s: %s \n+ %1\$s: %s",
                $item['key'],
                scalarToStr($item['value']),
                scalarToStr($item['value2'])
            ),
            default => throw new \Exception('Unknown type ' . $item['type'])
        };
    }, $diff);
    return sprintf("{\n%s\n}", implode("\n", $items));
}

function scalarToStr(mixed $value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    return (string)$value;
}

function getContent(string $filename): array
{
    $filename = normalizePath($filename);

    $content = file_get_contents($filename);
    if ($content === false) {
        throw new \Exception('can\'t get content for file' . $filename);
    }
    return json_decode($content, true);
}

function normalizePath(string $path): string
{
    if (!str_starts_with($path, '/')) {
        $path = implode('/', [__DIR__, '..', '..', $path]);
    }

    $filename = realpath($path);
    if (!$filename) {
        throw new \InvalidArgumentException("$path not exist");
    }

    return $filename;
}
