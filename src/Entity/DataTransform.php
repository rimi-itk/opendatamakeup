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
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataTransformRepository")
 * @Gedmo\Loggable
 * @AppAssert\ValidTransform
 */
class DataTransform
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $transformer;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $transformerArguments = [];

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition
     * @Gedmo\Versioned
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DataWrangler", inversedBy="transforms")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\SortableGroup
     * @Gedmo\Versioned
     */
    private $dataWrangler;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getTransformer(): ?string
    {
        return $this->transformer;
    }

    public function setTransformer(string $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function getTransformerArguments(): ?array
    {
        return $this->transformerArguments;
    }

    public function setTransformerArguments(array $transformerArguments): self
    {
        $this->transformerArguments = $transformerArguments;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
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
}
