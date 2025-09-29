<?php

namespace ActTraining\LaravelModelVersions\Tests\Support;

class TestModelWithVersionableAttributes extends TestModel
{
    protected $versionableAttributes = ['name', 'data'];
}