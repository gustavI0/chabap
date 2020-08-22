<?php


namespace App\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;

interface ImageInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return File|null
     */
    public function getImageFile(): ?File;

    /**
     * @return EmbeddedFile|null
     */
    public function getImage(): ?EmbeddedFile;

}