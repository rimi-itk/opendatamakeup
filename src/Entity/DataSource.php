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

    public const FORMAT_CSV = 'csv';
    public const FORMAT_JSON = 'json';
//    public const FORMAT_XML = 'xml';

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
    private $format;

    /**
     * @ORM\Column(type="integer")
     * @Assert\PositiveOrZero
     */
    private $ttl;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastReadAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jsonRoot;

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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        if (!\in_array($format, [static::FORMAT_CSV, static::FORMAT_JSON], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid type: %s', $format));
        }
        $this->format = $format;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @param mixed $ttl
     *
     * @return DataSource
     */
    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastReadAt(): ?\DateTime
    {
        return $this->lastReadAt;
    }

    /**
     * @param mixed $lastReadAt
     *
     * @return DataSource
     */
    public function setLastReadAt(\DateTime $lastReadAt = null): self
    {
        $this->lastReadAt = $lastReadAt;

        return $this;
    }

    public function getJsonRoot(): ?string
    {
        return $this->jsonRoot;
    }

    public function setJsonRoot(string $jsonRoot = null): self
    {
        $this->jsonRoot = $jsonRoot;

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
