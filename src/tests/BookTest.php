<?php

use PHPUnit\Framework\TestCase;
require_once (dirname(__DIR__)."/../src/app/BookCover.php");

class BookTest extends TestCase
{
    public function testIfImageIsInSyndetics()
    {
        $syndeticsTestUrl = 'https://syndetics.com/index.aspx?isbn=0131101633/LC.JPG&client=miamih';

        $bookUrl = new BookCover('miamih', '01-311-01633', 'LC');

        $urlFromSyndetics = $bookUrl->getCover();

        $this->assertEquals($syndeticsTestUrl, $urlFromSyndetics);
    }

    public function testIfImageIsInGoogleBooks()
    {
        $googleTestUrl = 'http://books.google.com/books/content?id=HnTvAAAAMAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api';

        $bookUrl = new BookCover('miamih', '0943396042', 'LC');

        $urlFromGoogle = $bookUrl->getCover();

        $this->assertEquals($googleTestUrl, $urlFromGoogle);
    }
}
