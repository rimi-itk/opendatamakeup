<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataWranglerRepository")
 * @Gedmo\Loggable
 */
class DataWrangler
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
     * @ORM\ManyToMany(targetEntity="App\Entity\DataSource", inversedBy="dataWranglers")
     */
    private $dataSources;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DataTransform", mappedBy="dataWrangler", cascade={"persist"}, orphanRemoval=true)
     * @Assert\Valid
     */
    private $transforms;

    public function __construct()
    {
        $this->dataSources = new ArrayCollection();
        $this->transforms = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection|DataSource[]
     */
    public function getDataSources(): Collection
    {
        return $this->dataSources;
    }

    public function addDataSource(DataSource $dataSource): self
    {
        if (!$this->dataSources->contains($dataSource)) {
            $this->dataSources[] = $dataSource;
        }

        return $this;
    }

    public function removeDataSource(DataSource $dataSource): self
    {
        if ($this->dataSources->contains($dataSource)) {
            $this->dataSources->removeElement($dataSource);
        }

        return $this;
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

    /**
     * @return Collection|DataTransform[]
     */
    public function getTransforms(): Collection
    {
        return $this->transforms;
    }

    public function addTransform(DataTransform $transform): self
    {
        if (!$this->transforms->contains($transform)) {
            $this->transforms[] = $transform;
            $transform->setDataWrangler($this);
        }

        return $this;
    }

    public function removeTransform(DataTransform $transform): self
    {
        if ($this->transforms->contains($transform)) {
            $this->transforms->removeElement($transform);
            // set the owning side to null (unless already changed)
            if ($transform->getDataWrangler() === $this) {
                $transform->setDataWrangler(null);
            }
        }

        return $this;
    }
}
