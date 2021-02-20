<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private const USERNAME = 'john';
    private const PASSWORD = 'maxsecure';

    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername(self::USERNAME);
        $encodedPassword = $this->encoder->encodePassword($user, self::PASSWORD);
        $user->setPassword($encodedPassword);

        $manager->persist($user);
         
        $manager->flush();
    }
}
