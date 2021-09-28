<?php declare(strict_types=1);

namespace app\core\Reader;

use app\core\Company;
use app\core\Exception\AresException;
use app\core\Exception\IcoException;
use app\core\Exception\InvalidResponse;
use app\core\Exception\NotFound;
use Exception;

/**
 * Basic URL data reader class with common functionality for all URL readers.
 *
 */
abstract class UrlReader
{
    public string $ico;

    /**
     * Check ICO validity.
     *
     * @param string $ico ico to check
     * @return bool true if ico is valid, otherwise false
     * @link https://phpfashion.com/jak-overit-platne-ic-a-rodne-cislo used full code from link
     */
    protected function isValid(string $ico) : bool
    {
        // be liberal in what you receive
        $ico = preg_replace('#\s+#', '', $ico);

        // má požadovaný tvar?
        if (!preg_match('#^\d{8}$#', $ico)) {
            return false;
        }

        // kontrolní součet
        $a = 0;
        for ($i = 0; $i < 7; $i++) {
            $a += $ico[$i] * (8 - $i);
        }

        $a = $a % 11;
        if ($a === 0) {
            $c = 1;
        } elseif ($a === 1) {
            $c = 0;
        } else {
            $c = 11 - $a;
        }

        return (int) $ico[7] === $c;
    }

    /**
     * Returns found company.
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
    abstract public function getData(string $ico) : Company;
}