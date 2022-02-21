<?php

namespace App\Service;

use App\Entity\Member;
use App\Entity\MemberGroup;
use App\Repository\MemberGroupRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\VObject\Component;
use Sabre\VObject\Splitter\VCard;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminMember
{
    private int $memberUpdated = 0;
    private int $memberCreated = 0;

    private EntityManagerInterface $entityManager;
    private MemberRepository $memberRepository;
    private MemberGroupRepository $groupRepository;

    /**
     * AdminMember constructor.
     * @param EntityManagerInterface $entityManager
     * @param MemberRepository $memberRepository
     * @param MemberGroupRepository $groupRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MemberRepository $memberRepository,
        MemberGroupRepository $groupRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->memberRepository = $memberRepository;
        $this->groupRepository = $groupRepository;
    }

    public function import(UploadedFile $file)
    {
        if ($file->guessExtension() === 'vcard') {
            $splitter = new VCard(
                fopen($file->getRealPath(), 'r'
                ));

            while ($vcard = $splitter->getNext()) {
                $member = $this->getMember($vcard);
                $member->setIsActive(true);
                if ($vcard->N) {
                    $fullName = explode(";", $vcard->N);
                    $member->setFirstName($fullName[1]);
                    $member->setLastName($fullName[0]);
                }
                if ($vcard->ADR) {
                    $addressFull = explode(";", $vcard->ADR);
                    $member->setAddress($addressFull[2]);
                    $member->setCity($addressFull[3]);
                    $member->setZip($addressFull[5]);
                }
                $this->setMemberGroup($vcard, $member);
                $member->setEmail($vcard->EMAIL ?? '');
                $member->setMobile1($vcard->getByType('TEL', 'CELL') ?? null);
                $member->setPhone1($vcard->getByType('TEL', 'home') ?? null);
            }
            $this->entityManager->flush();
        }
    }

    private function getMember(?Component $vcard): Member
    {
        $member = null;
        if ($vcard) {
            if ($vcard->EMAIL) {
                $member = $this->memberRepository->findOneBy(['email' => $vcard->EMAIL]);
            } elseif ($vcard->N) {
                $fullName = explode(";", $vcard->N);
                $member = $this->memberRepository->findOneBy([
                    'firstName' => $fullName[1],
                    'lastName' => $fullName[0]
                ]);
            }
        }
        if (!$member) {
            $member = new Member();
            $this->memberCreated++;
            $this->entityManager->persist($member);
        } else {
            $this->memberUpdated++;
        }
        return $member;
    }

    private function setMemberGroup(?Component $vcard, Member $member)
    {
        if ($vcard) {
            $categories = explode(",", $vcard->CATEGORIES);
            foreach ($categories as $category) {
                $memberGroup = $this->groupRepository->findOneBy(['name' => $category]);
                if (null === $memberGroup && !empty($category)) {
                    $memberGroup = new MemberGroup();
                    $memberGroup->setName($category);
                    $this->entityManager->persist($memberGroup);
                    $this->entityManager->flush();
                }
                if ($memberGroup) {
                    $member->addGroup($memberGroup);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getMemberUpdated(): int
    {
        return $this->memberUpdated;
    }

    /**
     * @return int
     */
    public function getMemberCreated(): int
    {
        return $this->memberCreated;
    }


}
