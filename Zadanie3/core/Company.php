<?php declare(strict_types=1);

namespace app\core;

use Exception;
use DateTime;

/**
 * Simple class represented company entity.
 *
 * @internal Created only for ARES testing, just simple entity ...
 */
class Company
{
    public string $ico;
    public string $dic;
    public string $companyName;
    public \DateTime $startDate;
    public string $legalForm;

    /**
     * Class constructor.
     *
     * @param string $ico ICO
     * @param string $dic DIC
     * @param string $companyName company name
     * @param string $startDate company start at
     * @param string $legalForm legal form
     * @throws Exception if error during object creation
     */
    public function __construct(string $ico, string $dic, string $companyName, string $startDate, string $legalForm)
    {
        $this->ico = $ico;
        $this->dic = $dic;
        $this->companyName = $companyName;
        $this->legalForm = $legalForm;
        $this->startDate = DateTime::createFromFormat('Y-m-d', $startDate);
    }

    /**
     * String object representation.
     *
     * @return string string representation
     */
    public function __toString() : string
    {
        return "CN: " . $this->companyName .
            " ICO: " . $this->ico .
            " DIC: " . $this->dic .
            " LF: " . $this->legalForm .
            " CRE: " . $this->startDate->format("Y-m-d");
    }

}