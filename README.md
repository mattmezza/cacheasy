cacheasy
=====

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mattmezza/cacheasy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mattmezza/cacheasy/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/mattmezza/cacheasy/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mattmezza/cacheasy/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/mattmezza/cacheasy/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mattmezza/cacheasy/build-status/master) 

> I hate slow APIs, I cache things on disk.

# Install

`composer require mattmezza/cacheasy`
# Usage

With string responses:

```php
$providerStrAPI = new class implements Cacheasy\StringProvider {
    public function get() : string
    {
        return (new SlowAPIsClient())->slowAPI();
    }
};
// ./cache is the cache path, 86400 is the time to live
$cache = new Cache("./cache", 86400);
// if slowAPI is not cached let's get the data and cache them
$result = $cache->getString("slowAPI", $providerStrAPI); # this is slow :(
$result2 = $cache->getString("slowAPI", $providerStrAPI); # this is blazing fast :)
echo $result2;
```

With JSON responses:

```php
$providerJsonAPI = new class implements Cacheasy\JsonProvider {
    public function get() : array
    {
        return (new SlowAPIsClient())->slowAPI();
    }
};
// ./cache is the cache path, 86400 is the time to live
$cache = new Cache("./cache", 86400);
// if slowAPI is not cached let's get the data and cache them
$result = $cache->getJson("slowjsonAPI", $providerJsonAPI); # this is slow :(
$result2 = $cache->getJson("slowjsonAPI", $providerJsonAPI); # this is blazing fast :)
echo $result2["property"];
```

# API

- `cacheString($key, $string) : string`: caches a string with key
- `cacheJson($key, $array) : array`: caches an array to json with key
- `hitString($key) : string`: tries to resume from cache a string with key
- `hitJson($key) : array`: tries to resume from cache a json with key
- `isCached($key) : bool`: checks if key is cached on disk and if it is not expired
- `getJson($key, $provider = null, bool $forceFresh = false) : array`: returns `hitJson(...)` if key is cached, calls provider otherwise. Throws exception if $provider is null and $key is not cached. If $forceFresh is set to `true` skips isCached check and calls the provider (ultimately caching the data).
- `getString($key, $provider = null, bool $forceFresh = false) : string`: returns `hitString(...)` if key is cached, calls provider otherwise. Throws exception if $provider is null and $key is not cached. If $forceFresh is set to `true` skips isCached check and calls the provider (ultimately caching the data).
- `invalidate($key) : void`: deletes the cached resource
- `invalidateAll() : void`: deletes all the cached resources

## Exceptions

`MissingProviderException`: when `get..(...)` is called for a non cached resource and no provider is passed, or when, even if the resource is cached, the method is invoked with null provider and with `true` force fresh values.
`NotCachedException`: when you wanna hit the cache but the resource is not cached yet.

# Development

- `git clone https://github.com/mattmezza/cacheasy.git`
- `cd cacheasy`
- `composer test`

##### Matteo Merola <mattmezza@gmail.com>