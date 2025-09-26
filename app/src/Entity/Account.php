<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint')]
    private int $balanceCents = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalanceCents(): int
    {
        return $this->balanceCents;
    }

    public function setBalanceCents(int $cents): self
    {
        $this->balanceCents = $cents;
        return $this;
    }

    public function addBalanse(int $cents): self
    {
        $this->balanceCents += $cents;
        return $this;
    }
}
