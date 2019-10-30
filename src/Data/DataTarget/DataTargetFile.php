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
use App\Annotation\Transform\Option;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DataTargetFile.
 *
 * @DataTarget(name="File", alias="file", description="Send to a file")
 */
class DataTargetFile extends AbstractDataTarget
{
    /**
     * @Option(name="File name", description="Absolute path the output file", type="string")
     */
    private $filename;

    /**
     * @Option(name="Format", description="", type="choice", choices={"JSON": "json", "Comma Separated Values (CSV)": "csv"})
     */
    private $format;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(SerializerInterface $serializer, Filesystem $filesystem)
    {
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
    }

    public function publish(array $rows, Collection $columns, array &$data)
    {
        $content = $this->serializer->serialize($rows, $this->format);
        $this->filesystem->dumpFile($this->filename, $content);
        $this->info(sprintf('%d row(s) written to %s', \count($rows), $this->filename));
    }
}
