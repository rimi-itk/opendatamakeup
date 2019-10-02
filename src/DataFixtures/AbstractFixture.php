<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractFixture extends Fixture
{
    /** @var string */
    protected $entityClass;

    /** @var string */
    protected $fixtureName;

    /** @var PropertyAccessor */
    protected $accessor;

    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string|null $fixtureName
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    protected function loadFixture(string $fixtureName = null)
    {
        if (null === $fixtureName) {
            if (null !== $this->fixtureName) {
                $fixtureName = $this->fixtureName;
            } else {
                $name = (new \ReflectionClass($this->entityClass))->getShortName();
                $fixtureName = $name.'.yaml';
            }
        }
        $basepath = __DIR__.'/Data';
        $content = file_get_contents($basepath.'/'.$fixtureName);

        return Yaml::parse($content);
    }

    public function load(ObjectManager $manager)
    {
        if (null === $this->entityClass) {
            throw new \RuntimeException('No class defined in '.static::class);
        }

        $fixtures = $this->loadFixture();
        $this->accessor = new PropertyAccessor();

        $metadata = $this->getMetadata($this->entityClass);
        foreach ($fixtures as $index => $data) {
            $entity = $this->buildEntity($data, $metadata);
            if (null !== $entity) {
                $idGenerator = null;
                $idGeneratorType = null;
                // If id has been set on entity we use that.
                // @TODO Use $metadata->getIdentifierFieldNames to check if all id fields have been set.
                if (method_exists($entity, 'getId') && null !== $entity->getId()) {
                    $idGenerator = $metadata->idGenerator;
                    $idGeneratorType = $metadata->generatorType;
                    $metadata->setIdGenerator(new AssignedGenerator());
                    $metadata->setIdGeneratorType($metadata::GENERATOR_TYPE_NONE);
                }
                $errors = $this->validator->validate($entity);
                if (\count($errors) > 0) {
                    throw new \RuntimeException((string) $errors);
                }
                $this->persist($entity, $manager);
                if (null !== $idGenerator && null !== $idGeneratorType) {
                    // Restore the id generator.
                    $metadata->setIdGenerator($idGenerator);
                    $metadata->setIdGeneratorType($idGeneratorType);
                }
            }

            if (isset($data['@id'])) {
                $this->addReference($data['@id'], $entity);
            }
        }
        $manager->flush();
    }

    protected function persist($object, ObjectManager $manager)
    {
        $manager->persist($object);
    }

    protected function buildEntity(array $data, ClassMetadata $metadata)
    {
        $className = $metadata->getName();
        $entity = new $className();
        foreach ($data as $propertyPath => $value) {
            if (0 === strpos($propertyPath, '@')) {
                continue;
            }

            // Convert references to actual entities for associations.
            if ($metadata->hasAssociation($propertyPath)) {
                if (\is_array($value) && $metadata->isCollectionValuedAssociation($propertyPath)) {
                    $targetEntityClass = $metadata->getAssociationTargetClass($propertyPath);
                    $value = array_map(function ($value) use ($targetEntityClass) {
                        if (\is_string($value)) {
                            return $this->getEntityReference($value);
                        } else {
                            $metadata = $this->getMetadata($targetEntityClass);

                            return $this->buildEntity($value, $metadata);
                        }
                    }, $value);
                } else {
                    $value = $this->getEntityReference($value);
                }
            } elseif (isset($metadata->fieldMappings[$propertyPath]['type'])) {
                $value = $this->convert($value, $metadata->fieldMappings[$propertyPath]['type']);
            }

            try {
                if ($metadata->isIdentifier($propertyPath)) {
                    $idProperty = new \ReflectionProperty($entity, $propertyPath);
                    $idProperty->setAccessible(true);
                    $idProperty->setValue($entity, $value);
                } else {
                    $this->accessor->setValue($entity, $propertyPath, $value);
                }
            } catch (\Exception $exception) {
                throw new \RuntimeException(sprintf('Cannot set property %s.%s on entity', \get_class($entity), $propertyPath));
            }
        }

        return $entity;
    }

    /**
     * @param $entity
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected function getMetadata($entity = null)
    {
        if (null === $entity) {
            $entity = $this->entityClass;
        }
        $className = \is_object($entity) ? \get_class($entity) : $entity;

        return $this->referenceRepository->getManager()->getClassMetadata($className);
    }

    protected function getEntityReference($reference)
    {
        if (0 === strpos($reference, '@')) {
            return $this->getReference(substr($reference, 1));
        }
        throw new \RuntimeException(sprintf(
            'Invalid reference: %s',
            $reference
        ));
    }

    /**
     * Convert a scalar value to the requested type.
     *
     * @param $value
     * @param $type
     *
     * @return mixed
     */
    protected function convert($value, $type)
    {
        switch ($type) {
            case 'datetime':
                return new \DateTime($value);
        }

        return $value;
    }
}
