<?php


namespace App\Entity;


/**
 * Interface PaymentInterface
 * @package App\Entity
 */
interface PaymentInterface
{

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return int|null
     */
    public function getAmount(): ?int;

    /**
     * @return bool|null
     */
    public function getSuccess(): ?bool;

    /**
     * @return \DateTimeInterface|null
     */
    public function getDate(): ?\DateTimeInterface;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

}