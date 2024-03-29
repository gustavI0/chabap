<?php


namespace App\Service;


use App\Entity\ProductInterface;

class ProductManager
{

    public function calculatePercentage(ProductInterface $product): int
    {
        $price = $product->getPrice();
        $currentContribution = $product->getCurrentContribution();

        return 100 - ((($price - $currentContribution) / $price) * 100);
    }

}