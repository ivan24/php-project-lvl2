<?php

namespace Php\Package\Tests\Utils;

trait FixtureLoader
{
    public function getFixtureFullPath($fixtureName)
    {
        $parts = [__DIR__, '..', 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function getFixturesContent($fixtureName): string
    {
        $path = $this->getFixtureFullPath($fixtureName);
        if (!file_exists($path)) {
            throw new \Exception("File $path not exist");
        }
        return file_get_contents($path);
    }
}