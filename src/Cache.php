<?php
declare(strict_types=1);

namespace Cache;

class Cache
{
    /**
     * The path where to save the cached things
     */
    private $path;
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;

    public function __construct(string $path = null)
    {
        $this->path = rtrim($path ?? getenv("CACHE_PATH"), "/");
    }

	/**
     * This methods tries to hit the cache and if the content is not present
     * or expired caches it for future usage
     * 
     * @param   string  $label  The key of the resource to cache
     * @param   StringProvider  $provider   The provider for the actual content
     */
	public function getString(string $label, StringProvider $provider) : string
	{
        // TODO add the try catch for the missing cache entry exception
		if($data = $this->hit($label)){
			return $data;
		} else {
			$data = $provider->get();
			$this->cache($label, $data);
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
		file_put_contents($this->cache_path . "/" . $this->safe_filename($label), $data);
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
		if($this->isCached($label)){
			$filename = $this->path . "/" . $label;
			return file_get_contents($filename);
		} else {
            throw new NotCachedExeption($label);
        }
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
		$filename = $this->path . "/" . $label;
        if(file_exists($filename) && (filemtime($filename) + $this->time >= time())) {
            return true;
        }
		return false;
    }
    
}
