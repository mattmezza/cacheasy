<?php

use PHPUnit\Framework\TestCase;
use Cache\Cache;

class CacheTest extends TestCase
{

    private $cache;

    public function setUp()
    {
        $this->cache = new Cache();
    }

    public function testGet()
    {

    }

    public function tearDown() 
    {

    }
}