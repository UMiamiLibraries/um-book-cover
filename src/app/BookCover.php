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
    private $googleBooksKey;
    private $isbn;
    private $imageSize;

    public function __construct($syndeticsClientCode, $isbn, $imageSize, $googleBooksKey) {
        $this->syndeticsClientCode = $syndeticsClientCode;
        $this->googleBooksKey = $googleBooksKey;
        $this->isbn = $isbn;
        $this->imageSize = $imageSize;
    }

    function getCover(){
		
        $result = '';
        $cover_path = $_SERVER["DOCUMENT_ROOT"]."/external_scripts/newitems/cover_cache/".$this->isbn.".jpg";
		
        if ($this->isCoverInChache($cover_path)){
            $result = "http://sp.library.miami.edu/external_scripts/newitems/cover_cache/".$this->isbn.".jpg";
        }else if (empty($result)) {
            $foundInSyndetics = $this->getCoverFromSyndetics($cover_path);

            if ($foundInSyndetics) {
                $result = "http://sp.library.miami.edu/external_scripts/newitems/cover_cache/".$this->isbn.".jpg";
            } else {
                $foundInGoogle = $this->getCoverFromGoogleBooks($cover_path);

                if ($foundInGoogle) {
                    $result = "http://sp.library.miami.edu/external_scripts/newitems/cover_cache/".$this->isbn.".jpg";
                }
            }
        }

        return $this->googleBooksKey;
    }

    private function getCoverFromSyndetics($local_destination)
    {
        if (!empty($this->isbn) && !empty($this->imageSize) && !empty($this->syndeticsClientCode)) {

            $imageUrl = 'https://syndetics.com/index.aspx?isbn=' . $this->isbn . '/' . $this->imageSize . '.JPG&client=' . $this->syndeticsClientCode;

            $imageValidated = $this->validateImageExists($this->isbn, $this->syndeticsClientCode);

            if ($imageValidated != false) {
                $this->download_cover($imageUrl, $local_destination);
                return true;
            }
        }
        return false;
    }

    private function getCoverFromGoogleBooks($local_destination)
    {
        if (!empty($this->isbn) && !empty($this->googleBooksKey)) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://www.googleapis.com/books/v1/volumes?key='.$this->googleBooksKey.'&q=isbn:' . $this->isbn);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $data = json_decode(curl_exec($curl));
            curl_close($curl);

            if (!is_null($data)) {
                if (isset($data->totalItems)) {
                    if ($data->totalItems > 0) {
                        if (isset($data->items[0]->volumeInfo->imageLinks->thumbnail)) {
                            $this->download_cover($data->items[0]->volumeInfo->imageLinks->thumbnail, $local_destination);
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function isCoverInChache($cover_local_path) {

        if(file_exists('file://' . $cover_local_path)) {
            return true;
        }
		
        return false;
    }

    private function download_cover($url, $local_destination) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $raw=curl_exec($curl);
        curl_close($curl);
        file_put_contents($local_destination, $raw);
    }

    private function validateImageExists($isbn, $syndeticsClientCode)
    {
        $xmlUrl = 'https://syndetics.com/index.aspx?isbn=' . $isbn . '/xml.xml&client=' . $syndeticsClientCode . '&type=rn12';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $xmlUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        $xml = null;
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($result);

        curl_close($curl);

        if (isset($xml)) {
            if (isset($xml->LC)) {
                return $xml->LC;
            } elseif (isset($xml->MC)) {
                return $xml->MC;
            } elseif (isset($xml->SC)) {
                return $xml->SC;
            } else {
                return false;
            }
        } else {
            return false;
        }

        libxml_use_internal_errors(false);
    }

}