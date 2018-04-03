<?php
declare(strict_types=1);

namespace Cacheasy;

/**
 * This class is responsible of providing a string resource
 */
interface StringProvider
{
    /**
     * This method is responsible of getting the data from a slow API for instance
     *
     * @return  string  The content to cache
     */
    public function get() : string;
}
