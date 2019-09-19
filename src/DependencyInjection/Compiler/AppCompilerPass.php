<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DependencyInjection\Compiler;

use App\Annotation\Transform;
use App\Transformer\AbstractTransformer;
use App\Transformer\Manager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('app.transformer');
        $transformers = array_filter($services, static function ($class) {
            return is_a($class, AbstractTransformer::class, true);
        }, ARRAY_FILTER_USE_KEY);

        AnnotationRegistry::registerLoader('class_exists');
        $reader = new CachedReader(
            new AnnotationReader(),
            new ArrayCache()
        );
        foreach ($transformers as $class => &$metadata) {
            $annotation = $reader->getClassAnnotation(new \ReflectionClass($class), Transform::class);
            if (null !== $annotation) {
                $metadata = $annotation->asArray();
            }
        }
        unset($metadata);

        $definition = $container->getDefinition(Manager::class);
        $definition->setArgument('$transformers', $transformers);
    }
}
