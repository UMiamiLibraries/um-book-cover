<?php
/**
 * Created by PhpStorm.
 * User: acarrasco
 * Date: 8/30/2016
 * Time: 1:19 PM
 */


class BookCover
{
    private $syndeticsClientCode;
    private $isbn;
    private $imageSize;

    public function __construct($syndeticsClientCode, $isbn, $imageSize) {
        $this->syndeticsClientCode = $syndeticsClientCode;
        $this->isbn = $this->validateISBN($isbn);
        $this->imageSize = $imageSize;
    }

    function getCover(){
        $result = $this->getCoverFromSyndetics();

        if (empty($result))
            $result = $this->getCoverFromGoogleBooks();

        return $result;
    }

    private function validateISBN($isbn)
    {
        $str = preg_replace('/[^0-9X]+/', '', $isbn);

        $length = strlen ($str);
        $result = '';

        if ($length == 10 || $length == 13)
            $result = $str;

        return $result;
    }

    private function getCoverFromSyndetics()
    {
        $result = '';

        if (!empty($this->isbn) && !empty($this->imageSize) && !empty($this->syndeticsClientCode)) {

            $imageUrl = 'https://syndetics.com/index.aspx?isbn=' . $this->isbn . '/' . $this->imageSize . '.JPG&client=' . $this->syndeticsClientCode;

            $imageInfo = @getimagesize($imageUrl);


            if ($imageInfo) {
                if (isset($imageInfo['mime'])) {
                    if ($imageInfo['mime'] === 'image/jpeg') {
                        $result = $imageUrl;
                    }
                }
            }
        }

        return $result;
    }

    private function getCoverFromGoogleBooks()
    {
        $result = '';

        if (!empty($this->isbn)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $this->isbn);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $data = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!is_null($data)) {
                if (isset($data->totalItems)) {
                    if ($data->totalItems > 0) {
                        if (isset($data->items[0]->volumeInfo->imageLinks->thumbnail)) {
                            $result = $data->items[0]->volumeInfo->imageLinks->thumbnail;
                        }
                    }
                }
            }
        }

        return $result;

    }

}