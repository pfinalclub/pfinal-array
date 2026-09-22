<?php

declare(strict_types=1);

namespace pf\arr;

function pipe(mixed $value, callable ...$steps): mixed
{
    foreach ($steps as $step) {
        $value = $step($value);
    }

    return $value;
}

function compose(callable ...$steps): callable
{
    return fn (mixed $value): mixed => pipe($value, ...array_reverse($steps, false));
}
