<?php

declare(strict_types=1);

namespace App\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public readonly array $methods;

    public function __construct(
        public readonly string $path,
        string|array $method = 'GET'
    ) {
        $this->methods = array_map(strtoupper(...), (array) $method);
    }
}
