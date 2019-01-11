<?php declare(strict_types = 1);

namespace Flaconi\Behat;

use Flaconi\Behat\Context\ClassResolver;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
class Extension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.
    }

    /**
     * @inheritDoc
     */
    public function getConfigKey()
    {
        return 'flaconi';
    }

    /**
     * @inheritDoc
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        // TODO: Implement initialize() method.
    }

    /**
     * @inheritDoc
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        // TODO: Implement configure() method.
    }

    /**
     * @inheritDoc
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(ClassResolver::class);
        $definition->addTag(ContextExtension::CLASS_RESOLVER_TAG);
        $container->setDefinition('flaconi.class_resolver', $definition);
    }

}