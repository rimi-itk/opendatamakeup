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
use FOS\UserBundle\Model\UserManagerInterface;

class UserFixture extends AbstractFixture
{
    protected $entityClass = User::class;

    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    protected function buildEntity(array $data, string $entityClass = null)
    {
        $entity = parent::buildEntity($data, $entityClass);

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
