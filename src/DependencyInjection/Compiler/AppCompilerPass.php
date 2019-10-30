<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DependencyInjection\Compiler;

use App\Annotation\DataTarget;
use App\Annotation\Transform;
use App\Data\DataTarget\AbstractDataTarget;
use App\Data\DataTarget\DataTargetManager;
use App\Transformer\AbstractTransformer;
use App\Transformer\TransformerManager;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new CachedReader(
            new AnnotationReader(),
            new ArrayCache()
        );

        $services = $container->findTaggedServiceIds('app.transformer');
        $transformers = array_filter($services, static function ($class) {
            return is_a($class, AbstractTransformer::class, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($transformers as $class => &$metadata) {
            $annotation = $reader->getClassAnnotation(new \ReflectionClass($class), Transform::class);
            if (null !== $annotation) {
                $metadata = $annotation->asArray();
                $metadata['id'] = $class;
            }
        }
        unset($metadata);

        $definition = $container->getDefinition(TransformerManager::class);
        $definition->setArgument('$transformers', $transformers);

        $services = $container->findTaggedServiceIds('app.data_target');
        $dataTargets = array_filter($services, static function ($class) {
            return is_a($class, AbstractDataTarget::class, true);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($dataTargets as $class => &$metadata) {
            $reflectionClass = new ReflectionClass($class);
            /** @var DataTarget */
            $annotation = $reader->getClassAnnotation($reflectionClass, DataTarget::class);
            if (null !== $annotation) {
                $annotation->options = [];
                $properties = $reflectionClass->getProperties();
                foreach ($properties as $property) {
                    if (null !== $propertyAnnotation = $reader->getPropertyAnnotation(
                        $property,
                        Transform\Option::class
                    )) {
                        $annotation->options[$property->getName()] = $propertyAnnotation;
                    }
                }
                $annotation->class = $class;
                $metadata = serialize($annotation);
            }
        }
        unset($metadata);

        $definition = $container->getDefinition(DataTargetManager::class);
        $definition->setArgument('$dataTargets', $dataTargets);
    }
}
