<?php
declare(strict_types=1);

namespace Cache;

class NotCachedException extends \Exception
{
    private $label;
    public function __construct(string $label)
    {
        parent::__construct("The resource `$label` you are trying to hit from cache is not cached yet.");
        $this->label = $label;
    }

    public function getLabel() : string
    {
        return $this->label;
    }
}
