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
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataSourceRepository")
 * @UniqueEntity(fields={"name", "url"})
 */
class DataSource
{
    use BlameableEntity;
    use TimestampableEntity;

    public const TYPE_CSV = 'csv';
    public const TYPE_JSON = 'json';
//    public const TYPE_XML = 'xml';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\DataWrangler", mappedBy="dataSource")
     */
    private $dataWranglers;

    public function __construct()
    {
        $this->dataWranglers = new ArrayCollection();
    }

    public function getId(): ?string
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!\in_array($type, [static::TYPE_CSV, static::TYPE_JSON], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid type: %s', $type));
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|DataWrangler[]
     */
    public function getDataWranglers(): Collection
    {
        return $this->dataWranglers;
    }

    public function addDataWrangler(DataWrangler $dataWrangler): self
    {
        if (!$this->dataWranglers->contains($dataWrangler)) {
            $this->dataWranglers[] = $dataWrangler;
            $dataWrangler->addDataSource($this);
        }

        return $this;
    }

    public function removeDataWrangler(DataWrangler $dataWrangler): self
    {
        if ($this->dataWranglers->contains($dataWrangler)) {
            $this->dataWranglers->removeElement($dataWrangler);
            $dataWrangler->removeDataSource($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name ?? static::class;
    }
}
