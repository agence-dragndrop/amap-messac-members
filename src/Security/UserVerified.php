<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVerified extends Voter
{
    public const ROLE_USER_VERIFIED = 'ROLE_USER_VERIFIED';
    private Security $security;

    /**
     * UserVerified constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::ROLE_USER_VERIFIED])) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }
        return $user->isVerified();
    }
}
