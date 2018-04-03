<?php
declare(strict_types=1);

namespace Cacheasy;

use Cacheasy\NotCachedException;
use Cacheasy\MissingProviderException;
use Cacheasy\StringProvider;
use Cacheasy\JsonProvider;

class Cache
{
    /**
     * The path where to save the cached things
     */
    private $path;
    // Length of time to cache a file (in seconds)
    public $ttl;

    public function __construct(string $path = null, int $ttl = 3600 * 24 * 1)
    {
        $this->path = rtrim($path ?? getenv("CACHE_PATH"), "/");
        $this->ttl = $ttl ?? getenv("CACHE_TTL");
    }

    /**
     * This methods tries to hit the cache and if the content is not present
     * or expired caches it for future usage
     *
     * @param   string  $label  The key of the resource to cache
     * @param   StringProvider  $provider   The provider for the actual content
     * @param   bool    $forceFresh     A bool indicating whether or not to force a fresh call
     *
     * @throws  MissingProviderException    Thrown when a provider needs to be called but null is passed.
     *
     * @return  string   The fetched and/or cached data
     */
    public function getString(string $label, StringProvider $provider = null, bool $forceFresh = false) : string
    {
        if ($forceFresh) {
            // skipping everything, the user requested fresh data
            // he wants to wait, we better cache the fresh data
            if (!$provider) {
                throw new MissingProviderException($label);
            }
            $data = $provider->get();
            $this->cacheString($label, $data);
            return $data;
        }
        // let's check if we've got something cached already...
        try {
            return $this->hitString($label);
        } catch (NotCachedException $e) {
            // Oooopsss... we ain't got anything, let's get the fresh data (and cache them)
            if (!$provider) {
                throw new MissingProviderException($label);
            }
            $data = $provider->get();
            $this->cacheString($label, $data);
            return $data;
        }
    }

    /**
     * This methods tries to hit the cache and if the content is not present
     * or expired caches it for future usage
     *
     * @param   string  $label  The key of the resource to cache
     * @param   JsonProvider  $provider   The provider for the actual content
     * @param   bool    $forceFresh     A bool indicating whether or not to force a fresh call
     *
     * @throws  MissingProviderException    Thrown when a provider needs to be called but null is passed.
     *
     * @return  array   The fetched and/or cached data
     */
    public function getJson(string $label, JsonProvider $provider = null, bool $forceFresh = false) : array
    {
        if ($forceFresh) {
            // skipping everything, the user requested fresh data
            // he wants to wait, we better cache the fresh data
            if (!$provider) {
                throw new MissingProviderException($label);
            }
            $data = $provider->get();
            $this->cacheJson($label, $data);
            return $data;
        }
        // let's check if we've got something cached already...
        try {
            return json_decode($this->hitString($label), true);
        } catch (NotCachedException $e) {
            // Oooopsss... we ain't got anything, let's get the fresh data (and cache them)
            if (!$provider) {
                throw new MissingProviderException($label);
            }
            $data = $provider->get();
            $this->cacheJson($label, $data);
            return $data;
        }
    }
    
    /**
     * Actually writes the content to a file
     *
     * @param   string  $label  The key of the resource to cache
     * @param   string  $data   The actual content of the resource to cache
     */
    public function cacheString(string $label, string $data) : void
    {
        $filename = $this->path . "/" . md5($label);
        if (file_exists($filename) && is_file($filename)) {
            unlink($filename);
        }
        file_put_contents($filename, $data);
    }

    /**
     * Actually writes the json content to a file
     *
     * @param   string  $label  The key of the resource to cache
     * @param   array  $data   The actual content of the resource to cache
     */
    public function cacheJson(string $label, array $data) : void
    {
        $this->cacheString($label, json_encode($data));
    }
    
    /**
     * Tries to check if the content is cached
     *
     * @param   string  $label  The key of the cached resource
     *
     * @throws  NotCachedExeption   If the requested resource is not cached.
     *
     * @return  string  The content of the cached resource
     */
    public function hitString(string $label) : string
    {
        if ($this->isCached($label)) {
            $filename = $this->path . "/" . md5($label);
            return file_get_contents($filename);
        } else {
            throw new NotCachedException($label);
        }
    }

    /**
     * Tries to check if the content is cached
     *
     * @param   string  $label  The key of the cached resource
     *
     * @throws  NotCachedExeption   If the requested resource is not cached.
     *
     * @return  array  The content of the cached resource
     */
    public function hitJson(string $label) : array
    {
        return json_decode($this->hitString($label), true);
    }
    
    /**
     * Checks if the content is cached or not
     *
     * @param   string  $label  The key of the element to cache
     *
     * @return  bool    Whether the $label resource is cached
     */
    public function isCached(string $label) : bool
    {
        $filename = $this->path . "/" . md5($label);
        if (file_exists($filename) && (filemtime($filename) + $this->ttl >= time())) {
            return true;
        }
        return false;
    }

    /**
     * Invalidates an entry by deleting the corresponding file
     *
     * @param   string  $label  The key of the element to invalidate
     *
     * @throws  NotCachedException  Thrown when the resource $label is not cached.
     */
    public function invalidate(string $label) : void
    {
        if (!$this->isCached($label)) {
            throw new NotCachedException($label);
        }
        $filename = $this->path . "/" . md5($label);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Invalidates all the cache entries by deleting the corresponding files
     */
    public function invalidateAll() : void
    {
        $files = glob($this->path . "/*"); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }
}
