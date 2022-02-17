<?php

namespace App\Entity;

use App\Repository\OrderDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderDetailRepository::class)
 */
class OrderDetail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $content;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderDetails")
     */
    private ?Order $purchaseOrder;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="orderDetails")
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $identifier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $fullName;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?array
    {
        return $this->content ? json_decode($this->content, true) : null;
    }

    public function setContent(?array $content): self
    {
        $this->content = json_encode($content);

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPurchaseOrder(): ?Order
    {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder(?Order $purchaseOrder): self
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }
}
