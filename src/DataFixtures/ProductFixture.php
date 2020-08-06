<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setName('Miroir');
        $product->setDescription('Test description');
        $product->setPrice(1200);
        $product->setPicture('image.jpg');
        $manager->persist($product);

        $product = new Product();
        $product->setName('Table');
        $product->setDescription('Test description');
        $product->setPrice(300);
        $product->setPicture('image.jpg');
        $manager->persist($product);

        $manager->flush();
    }
}
