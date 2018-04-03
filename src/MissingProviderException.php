<?php
declare(strict_types=1);

namespace Cacheasy;

class MissingProviderException extends \Exception
{
    private $label;
    public function __construct(string $label)
    {
        parent::__construct("The content of the resource `$label` must be provided by some provider, but no provider is specified for it.");
        $this->label = $label;
    }

    public function getLabel() : string
    {
        return $this->label;
    }
}
