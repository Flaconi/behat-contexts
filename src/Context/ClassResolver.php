<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\ContextClass\ClassResolver as ClassResolverInterface;
use function explode;
use function sprintf;
use function strpos;
use function ucfirst;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
final class ClassResolver implements ClassResolverInterface
{
    /**
     * @param string $contextString
     *
     * @return bool
     */
    public function supportsClass($contextString): bool
    {
        return strpos($contextString, 'flaconi:') === 0;
    }

    /**
     * @param string $contextClass
     *
     * @return string
     */
    public function resolveClass($contextClass): string
    {
        return sprintf('Flaconi\Behat\Context\%sContext', ucfirst(explode(':', $contextClass)[1]));
    }
}
