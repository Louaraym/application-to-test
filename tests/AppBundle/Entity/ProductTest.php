<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @dataProvider pricesForFoodProduct
     * @param $price
     * @param $expectedTva
     */
    public function testComputeTVAFoodProduct($price, $expectedTva)
    {
        $product = new Product('a product', Product::FOOD_PRODUCT, $price);

        $this->assertSame($expectedTva, $product->computeTVA());
    }

    public function testComputeTVAOtherProduct()
    {
        $product = new Product('other product', 'other product type', 20);

        $this->assertSame(3.92, $product->computeTVA());
    }

    public function testNegativePriceComputeTVA()
    {
        $product = new Product('a product', Product::FOOD_PRODUCT, -20);

        $this->expectException('LogicException');

        $product->computeTVA();
    }

    public function pricesForFoodProduct()
    {
        return [
            [0, 0.0],
            [20, 1.1],
            [100, 5.5]
        ];
    }
}