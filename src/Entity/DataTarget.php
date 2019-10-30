<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataSourceRepository")
 * @UniqueEntity(fields={"name", "url"})
 */
class DataTarget
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $dataTarget;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotBlank
     *
     * @var array
     */
    private $dataTargetOptions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataWrangler", inversedBy="dataTargets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataWrangler;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataTarget(): ?string
    {
        return $this->dataTarget;
    }

    /**
     * @param mixed $dataTarget
     *
     * @return DataTarget
     */
    public function setDataTarget($dataTarget): self
    {
        $this->dataTarget = $dataTarget;

        return $this;
    }

    /**
     * @return array
     */
    public function getDataTargetOptions(): array
    {
        return $this->dataTargetOptions;
    }

    /**
     * @param array $dataTargetOptions
     *
     * @return DataTarget
     */
    public function setDataTargetOptions(array $dataTargetOptions): self
    {
        $this->dataTargetOptions = $dataTargetOptions;

        return $this;
    }

    public function __toString()
    {
        return $this->dataTarget ?? static::class;
    }

    public function getDataWrangler(): ?DataWrangler
    {
        return $this->dataWrangler;
    }

    public function setDataWrangler(?DataWrangler $dataWrangler): self
    {
        $this->dataWrangler = $dataWrangler;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
