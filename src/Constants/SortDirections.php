<?php

namespace Lelastico\Constants;

final class SortDirections
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    public static function getAll(): array
    {
        return [
            self::ASC,
            self::DESC,
        ];
    }
}
