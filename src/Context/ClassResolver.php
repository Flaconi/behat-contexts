<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\ContextClass\ClassResolver as ClassResolverInterface;
use function explode;
use function ucfirst;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
class ClassResolver implements ClassResolverInterface
{
    /**
     * @inheritDoc
     */
    public function supportsClass($contextString)
    {
        return (strpos($contextString, 'flaconi:') === 0);
    }

    /**
     * @inheritDoc
     */
    public function resolveClass($contextClass)
    {
        return sprintf('Flaconi\Behat\Context\%sContext', ucfirst(explode(':', $contextClass)[1]));
    }

}