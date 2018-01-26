<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\AntiSpamBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('core23_antispam');

        $this->addTimeSection($node);
        $this->addHoneypotSection($node);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addTimeSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('time')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('min')->defaultValue(5)->end()
                        ->integerNode('max')->defaultValue(3600)->end()
                        ->booleanNode('global')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addHoneypotSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('honeypot')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('field')->defaultValue('email_address')->end()
                        ->scalarNode('class')
                            ->defaultValue('hidden')
                            ->info('CSS class to hide the honeypot. If not set a "style:hidden" attribute ist set.')
                        ->end()
                        ->booleanNode('global')->defaultFalse()->end()
                        ->scalarNode('provider')->defaultValue('core23_antispam.provider.session')->end()
                    ->end()
                ->end()
            ->end();
    }
}
