<?php declare(strict_types=1);

namespace app;

class SuperFaktura
{
    /**
     * Print data with SuperFaktura format.
     *
     * @param array $data
     */
    public function print(array $data)
    {
        echo implode(PHP_EOL, $data);
    }

    /**
     * Compute data.
     *
     * @return array messages for printing
     */
    public function computeData() : array
    {
        $data = [];
        for($i = 1; $i <= 100; $i++) {

            if($i % 15 === 0) {
                $data[] = "SuperFaktura";
                continue;
            } else if($i % 5 === 0) {
                $data[] = "Faktura";
                continue;
            } else if($i % 3 === 0) {
                $data[] = "Super";
                continue;
            } else {
                $data[] = strval($i);
            }
        }

        return $data;
    }

    /**
     * Compute data - another version.
     *
     * @return array messages for printing
     */
    public function computeDataAlt() : array
    {
        $data = [];
        for($i = 1; $i <= 100; $i++) {

            $result = "";

            if($i % 3 === 0) {
                $result .= "Super";
            }

            if($i % 5 === 0) {
                $result .= "Faktura";
            }

            if($i % 3 !== 0 && $i % 5 !== 0) {
                $result .= $i;
            }

            $data[] = $result;
        }

        return $data;
    }
}