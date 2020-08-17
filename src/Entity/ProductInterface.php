<?php


namespace App\Entity;


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
     * @return File|null
     */
    public function getImageFile(): ?File;

    /**
     * @return EmbeddedFile|null
     */
    public function getImage(): ?EmbeddedFile;

    /**
     * @return int|null
     */
    public function getCurrentContribution(): ?int;
}