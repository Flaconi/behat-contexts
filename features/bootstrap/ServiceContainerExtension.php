<?php declare(strict_types=1);

namespace Flaconi\Behat\Tests;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\HelperContainer\Argument\ServicesResolver;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use function dirname;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use function var_dump;

final class ServiceContainerExtension implements Extension
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
        return 'flaconi_test';
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

        $container->setParameter('paths.base_org', dirname(__DIR__, 2));

        $definition = new Definition(ServicesResolver::class, array(
            new Reference('service_container')
        ));
        $definition->addTag(ContextExtension::ARGUMENT_RESOLVER_TAG, array('priority' => 0));
        $container->setDefinition('flaconi_behat_test.context.argument.service_resolver', $definition);
    }

}