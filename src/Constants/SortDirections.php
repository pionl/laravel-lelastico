<?php

declare(strict_types=1);

namespace Lelastico\Constants;

final class SortDirections
{
    /**
     * @var string
     */
    public const ASC = 'asc';

    /**
     * @var string
     */
    public const DESC = 'desc';

    public static function getAll(): array
    {
        return [self::ASC, self::DESC];
    }
}
