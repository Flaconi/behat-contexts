<?php

declare(strict_types=1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\ContextClass\ClassResolver as ClassResolverInterface;
use function explode;
use function Safe\sprintf;
use function strpos;
use function ucfirst;

final class ClassResolver implements ClassResolverInterface
{
    /**
     * @param string $contextString
     */
    public function supportsClass($contextString) : bool
    {
        return strpos($contextString, 'flaconi:') === 0;
    }

    /**
     * @param string $contextClass
     */
    public function resolveClass($contextClass) : string
    {
        return sprintf('Flaconi\Behat\Context\%sContext', ucfirst(explode(':', $contextClass)[1]));
    }
}
