<?php

namespace ActTraining\LaravelModelVersions\Tests\Support;

class TestModelWithNonVersionableAttributes extends TestModel
{
    protected $nonVersionableAttributes = ['description', 'is_active'];
}
