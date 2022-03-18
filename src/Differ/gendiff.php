<?php

namespace Differ;

use function Functional\map;

const REMOVE = 'remove';
const ADD = 'add';
const UNCHANGED = 'unchanged';
const CHANGED = 'changed';

function genDiff(array $first, array $second): array
{
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

    return $diff;
}

function diffToStr(array $diff, string $format): string
{
    $items = array_map(function ($item) {
        return match ($item['type']) {
            ADD => diffItemToStr('+', $item['key'], $item['value']),
            UNCHANGED => diffItemToStr(' ', $item['key'], $item['value']),
            REMOVE => diffItemToStr('-', $item['key'], $item['value']),
            CHANGED => sprintf(
                "%s\n%s",
                diffItemToStr('-', $item['key'], $item['value']),
                diffItemToStr('+', $item['key'], $item['value2']),
            ),
            default => throw new \Exception('Unknown type ' . $item['type'])
        };
    }, $diff);

    return sprintf("{\n%s\n}", implode("\n", $items));
}

function diffItemToStr(string $sign, string $key, mixed $value): string
{
    return sprintf('%s %s: %s', $sign, $key, scalarToStr($value));
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
