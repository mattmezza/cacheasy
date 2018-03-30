cacheasy
=====

> I hate slow APIs, I cache things on disk.

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
- `getJson($key, $provider = null) : array`: returns `hitJson(...)` if key is cached, calls provider otherwise. Throws exception if $provider is null and $key is not cached
- `getString($key, $provider = null) : string`: returns `hitString(...)` if key is cached, calls provider otherwise. Throws exception if $provider is null and $key is not cached


##### Matteo Merola <mattmezza@gmail.com>