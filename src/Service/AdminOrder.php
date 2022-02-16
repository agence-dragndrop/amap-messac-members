<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\MemberRepository;
use App\Repository\OrderDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AdminOrder
{
    public const EXCLUDE_COLUMNS = [
        "FULLNAME",
        "AMOUNT"
    ];
    private MemberRepository $memberRepository;
    private OrderDetailRepository $detailRepository;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private SerializerInterface $serializer;

    /**
     * AdminOrder constructor.
     * @param MemberRepository $memberRepository
     * @param OrderDetailRepository $detailRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        MemberRepository $memberRepository,
        OrderDetailRepository $detailRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer
    ) {
        $this->memberRepository = $memberRepository;
        $this->detailRepository = $detailRepository;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
    }

    public function mapMember(Order $order)
    {
        $file = $order->getFile();
        if (null === $file) {
            return;
        }
        $filePath = $this->parameterBag->get('order_file_dir') . "/" . $file;
        $context = [
            CsvEncoder::DELIMITER_KEY => ';',
            CsvEncoder::ENCLOSURE_KEY => '"',
            CsvEncoder::ESCAPE_CHAR_KEY => '\\',
            CsvEncoder::KEY_SEPARATOR_KEY => ',',
        ];
        $csv = $this->serializer->decode(file_get_contents($filePath), 'csv', $context);
        foreach ($csv as $key => $data) {
            $identifier = ($key + 1);
            $orderDetail = $this->detailRepository->findOneBy([
                'identifier' => $identifier,
                'purchaseOrder' => $order
            ]);
            if (null === $orderDetail) {
                $orderDetail = new OrderDetail();
                $orderDetail->setIdentifier($identifier);
                $this->entityManager->persist($orderDetail);
            }
            if (null === $orderDetail->getMember()) {
                $member = $this->memberRepository->findMembersByFullName($data['FULLNAME']);
                if (null !== $member) {
                    $orderDetail->setMember($member);
                }
            }
            $orderDetail
                ->setFullName($data['FULLNAME'])
                ->setPurchaseOrder($order)
                ->setAmount((float)str_replace(",", ".", $data['AMOUNT']))
                ->setContent($this->setContent($data));
        }
        $this->entityManager->flush();
    }

    private function setContent(array $data)
    {
        $content = $data;
        foreach (self::EXCLUDE_COLUMNS as $COLUMN) {
            unset($content[$COLUMN]);
        }
        return $content;
    }
}

