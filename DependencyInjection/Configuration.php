<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ez_core_extra');

        $rootNode
            ->children()
                ->arrayNode('design')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('list')
                            ->useAttributeAsKey('design_name')
                            ->example(['my_design' => ['theme1', 'theme2']])
                            ->prototype('array')
                                ->info('A design is a labeled collection of themes. Theme order defines the fallback order.')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->arrayNode('override_paths')
                            ->info('Directories to add to the override list. Those directories will be checked before theme directories.')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->scalarNode('design')
                ->cannotBeEmpty()
                ->info('Name of the design to use. Must be one of the declared ones in the "design" key.')
            ->end()
            ->arrayNode('twig_globals')
                ->info('Variables available in all Twig templates for current SiteAccess.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('variable_name')
                ->example(array('foo' => '"bar"', 'pi' => 3.14))
                ->prototype('variable')->end()
            ->end();

        return $treeBuilder;
    }
}
