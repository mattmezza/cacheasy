<?php
declare(strict_types=1);

namespace Cacheasy;

/**
 * This class is responsible of providing a string resource
 */
interface JsonProvider
{
    /**
     * This method is responsible of getting the data from a slow API for instance
     * 
     * @return  array  The content to cache
     */
    public function get() : array;
}