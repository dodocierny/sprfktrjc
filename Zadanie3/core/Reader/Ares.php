<?php declare(strict_types=1);

namespace app\core\Reader;

use app\core\Company;
use app\core\Exception\AresException;
use app\core\Exception\IcoException;
use app\core\Exception\InvalidResponse;
use app\core\Exception\NotFound;
use Exception;
use SimpleXMLElement;

class Ares extends UrlReader
{
    protected string  $defaultUrl = "http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=";

    /**
     * Returns company data from ARES database.
     *
     * @param string $ico ico
     * @return Company company entity
     * @throws IcoException if ICO has invalid format
     * @throws InvalidResponse if the data from the register is not in the expected format
     * @throws IcoException if ICO has invalid format
     * @throws AresException if datasource ARES is unavailable
     * @throws NotFound if ICO not found
     * @throws Exception if any other error occurred
     */
    public function getData(string $ico): Company
    {
        if($this->isValid($ico) === false) {
            throw new IcoException("Invalid ICO format: $ico");
        }

        $url = $this->getPath($ico);
        $xmlData = $this->curlRequest($url);
        $parsedData = $this->parseResponse($xmlData, $ico);

        if(($parsedData->status === 'ICO not found.')) {
            throw new NotFound($parsedData->status);
        }

        if(($parsedData->status !== 'OK')) {
            throw new InvalidResponse($parsedData->status);
        }

        return $this->createCompany($parsedData);
    }

    /**
     * Helper for create company object from parsed data.
     *
     * @param object $parsedData
     * @return Company company
     * @throws Exception if an error occurred during object creation
     */
    protected function createCompany(object $parsedData) : Company
    {
       return new Company(
           $parsedData->ico,
           $parsedData->dic,
           $parsedData->name,
           $parsedData->created_at,
           $parsedData->legal_form
       );
    }

    /**
     * Return URL for Ares service.
     *
     * @param string $ico ico
     * @return string full URL for data connect
     */
    protected function getPath(string $ico): string
    {
        return $this->defaultUrl . $ico;
    }

    /**
     * Make a request to specified URL.
     *
     * @param string $url url
     * @return SimpleXMLElement XML data
     * @throws AresException if can't ARES connect
     */
    protected function curlRequest(string $url) : SimpleXMLElement
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($curl);

        if ($data) {
            curl_close($curl);
            return simplexml_load_string($data);
        } else {
            throw new AresException("Can't read URL data source: " . curl_error($curl));
        }
    }

    /**
     * Parse XML response from ARES and return data as array.
     *
     * @param SimpleXMLElement $xml xml data
     * @return object parsed data - there is always a property with the name status - if value is OK parse
     *         process was correct, otherwise property status contains error message
     * @link https://wwwinfo.mfcr.cz/ares/ares_xml.html.cz ARES spec
     * @link https://webtrh.cz/279860-script-nacitani-dat-ares-jquery used some
     *       code and inspiration from link
     */
    protected function parseResponse(SimpleXMLElement $xml, string $ico) : object
    {
        $parsedData = [];

        $ns = $xml->getDocNamespaces();

        if(empty($ns["are"]) || empty($ns["D"])) {
            $parsedData["status"] = "Mismatch XML data.";
            return (object)$parsedData;
        }

        $data = $xml->children($ns["are"]);
        $el = $data->children($ns["D"])->VBAS;

        if (strval($el->ICO) === $ico) {
            $parsedData["ico"] = strval($el->ICO);
            $parsedData["dic"] = strval($el->DIC);
            $parsedData["created_at"] = strval($el->DV);
            $parsedData["name"] = strval($el->OF);
            $parsedData["legal_form"] = strval($el->PF->NPF);
            $parsedData["headquarters"] = $el->AD->UC . ", " . $el->AD->PB;
            $parsedData["status"] = "OK";
        } else {
            $parsedData["status"] = "ICO not found.";
        }

        return (object) $parsedData;
    }

}