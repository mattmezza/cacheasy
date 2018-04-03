<?php

use PHPUnit\Framework\TestCase;
use Cacheasy\Cache;
use Cacheasy\StringProvider;
use Cacheasy\NotCachedException;
use Cacheasy\MissingProviderException;

class CacheTest extends TestCase
{

    private $cache;

    public function setUp()
    {
        $this->cache = new Cache("tests/cache", 2);
    }

    public function testGet()
    {
        $label = "prova";
        $resourceOracle = "prova";
        // creating the cached resource
        file_put_contents("tests/cache/" . md5($label), $resourceOracle);
        // getting it from the cache
        $resource = $this->cache->getString($label);
        $this->assertEquals($resourceOracle, $resource);
        // trying the provider, we are trying to hit the cache for $label2 (which is not set)
        // hence Cache should invoke the provider and cache the oracle
        $label2 = "prova2";
        $resource2 = $this->cache->getString($label2, $this->createStringProvider($resourceOracle));
        $this->assertEquals($resourceOracle, $resource2);
        $this->assertTrue(file_exists("tests/cache/" . md5($label2)));
    }

    public function testGetWithForce()
    {
        $resourceOracle = "prova";
        // trying the provider, we are trying to hit the cache for $label2 (which is not set)
        // hence Cache should invoke the provider and cache the oracle
        $label2 = "prova2";
        $resource2 = $this->cache->getString($label2, $this->createStringProvider($resourceOracle));
        $this->assertEquals($resourceOracle, $resource2);
        $this->assertTrue(file_exists("tests/cache/" . md5($label2)));
        // now the resource has been cached on file
        // we will try to call with forceFresh with custom provider so the file should be overwritten
        $resource3 = $this->cache->getString($label2, $this->createStringProvider("dio"), true);
        $this->assertEquals("dio", $resource3);
        $this->assertTrue(file_exists("tests/cache/" . md5($label2)));
        $this->assertEquals("dio", file_get_contents("tests/cache/". md5($label2)));
    }

    public function testNotCachedMissingProviderGet()
    {
        $this->expectException(MissingProviderException::class);
        $resource = $this->cache->getString("dio");
    }

    public function testNotCachedHit()
    {
        $this->expectException(NotCachedException::class);
        $resource = $this->cache->hitString("dio");
    }

    public function testIsCached()
    {
        $this->assertFalse($this->cache->isCached("dio"));
        file_put_contents("tests/cache/" . md5("dio2"), "dio3");
        $this->assertTrue($this->cache->isCached("dio2"));
    }

    public function testCache()
    {
        $this->assertFalse($this->cache->isCached("dio4"));
        $this->cache->cacheString("dio4", "dio5");
        $this->assertTrue($this->cache->isCached("dio4"));
        $resource = $this->cache->getString("dio4");
        $this->assertEquals($resource, "dio5");

        $jsonStr = '{"prova":"provavalue","provakey2":"provavalue2"}';
        $jsonOracle = json_decode($jsonStr, true);
        $this->assertFalse($this->cache->isCached("json1"));
        $this->cache->cacheJson("json1", json_decode($jsonStr, true));
        $this->assertTrue($this->cache->isCached("json1"));
        $resource = $this->cache->getJson("json1");
        $this->assertEquals($jsonOracle, $resource);
    }

    public function testExpired()
    {
        $value = "try";
        $key = "nice";
        $this->cache->cacheString($key, $value);
        $this->assertEquals($value, $this->cache->getString($key));
        sleep(3);
        // the cache now is expired
        try {
            // this should fail
            $res = $this->cache->hitString($key);
        } catch (\Exception $e) {
            // the exc should match
            $this->assertInstanceOf(NotCachedException::class, $e);
            // ok, I am caching again
            $this->cache->cacheString($key, $value);
        }
        sleep(3);
        // the cache now is expired, again
        // this should realize that, and it should cache it again
        $this->assertEquals($value, $this->cache->getString($key, $this->createStringProvider($value)));
        // should be cached here
        $this->assertEquals($value, $this->cache->getString($key));
    }

    public function testInvalidate()
    {
        $name = md5("prova");
        file_put_contents("tests/cache/$name", "prova");
        $files = glob('tests/cache/*'); // get all file names
        $this->assertEquals(1, count($files));
        $this->cache->invalidate("prova");
        $files2 = glob('tests/cache/*'); // get all file names
        $this->assertEquals(0, count($files2));
    }

    public function testInvalidateNonExistantFile()
    {
        $this->expectException(NotCachedException::class);
        $name = md5("prova");
        file_put_contents("tests/cache/$name", "prova");
        $files = glob('tests/cache/*'); // get all file names
        $this->assertEquals(1, count($files));
        $this->cache->invalidate("provad");
    }

    public function testInvalidateAll()
    {
        $name = md5("prova");
        file_put_contents("tests/cache/$name", "prova");
        $files = glob('tests/cache/*'); // get all file names
        $this->assertEquals(1, count($files));
        $this->cache->invalidateAll();
        $files2 = glob('tests/cache/*'); // get all file names
        $this->assertEquals(0, count($files2));
    }

    public function tearDown()
    {
        // remove all files
        $files = glob('tests/cache/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    private function createStringProvider($oracle)
    {
        return new class($oracle) implements StringProvider {
            private $oracle;
            public function __construct($oracle)
            {
                $this->oracle = $oracle;
            }
            public function get() : string
            {
                return $this->oracle;
            }
        };
    }
}
