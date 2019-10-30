<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\DataTarget;

use App\Annotation\DataTarget;
use App\Traits\LogTrait;
use App\Transformer\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataTargetManager
{
    use LogTrait;
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $dataTargets;

    /**
     * @var array
     */
    private $aliases;

    public function __construct(ContainerInterface $container, array $dataTargets)
    {
        $this->setContainer($container);
        $this->dataTargets = array_map(function (string $s) {
            return unserialize($s, [DataTarget::class]);
        }, $dataTargets);
        $this->aliases = array_combine(array_column($this->dataTargets, 'alias'), array_keys($this->dataTargets));
    }

    /**
     * @return DataTarget[]
     */
    public function getDataTargets(): array
    {
        return $this->dataTargets;
    }

    /**
     * @param string     $name
     * @param array|null $options
     *
     * @return DataTarget
     */
    public function getDataTarget(string $name, array $options = null): AbstractDataTarget
    {
        $dataTargets = $this->getDataTargets();
        if (\array_key_exists($name, $this->aliases)) {
            $name = $this->aliases[$name];
        }

        if (!\array_key_exists($name, $dataTargets)) {
            throw new InvalidArgumentException(sprintf('Data target with name "%s" does not exist', $name));
        }

        /** @var AbstractDataTarget */
        $dataTarget = $this->container->get($name)->setMetadata($dataTargets[$name]);
        if (null !== $options) {
            $dataTarget->setOptions($options);
        }

        return $dataTarget;
    }
}
