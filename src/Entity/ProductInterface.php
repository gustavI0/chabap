<?php


namespace App\Entity;


use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;

/**
 * Interface ProductInterface
 * @package App\Entity
 */
interface ProductInterface
{

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return int|null
     */
    public function getPrice(): ?int;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @return int|null
     */
    public function getCurrentContribution(): ?int;

    /**
     * @return Collection|null
     */
    public function getPayments(): ?Collection;

    /**
     * @return Collection|null
     */
    public function getImages(): ?Collection;
}