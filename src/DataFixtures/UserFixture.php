<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFixture extends AbstractFixture
{
    protected $entityClass = User::class;

    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(ValidatorInterface $validator, UserManagerInterface $userManager)
    {
        parent::__construct($validator);
        $this->userManager = $userManager;
    }

    protected function buildEntity(array $data, ClassMetadata $metadata)
    {
        $entity = parent::buildEntity($data, $metadata);

        if ($entity instanceof User) {
            if (isset($data['password'])) {
                $entity->setPlainPassword($data['password']);
            }
        }

        return $entity;
    }

    protected function persist($object, ObjectManager $manager)
    {
        $this->userManager->updateUser($object, false);
    }
}
