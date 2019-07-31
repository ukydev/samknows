<?php
declare(strict_types=1);

namespace App\Service;

class MetricsService
{
    const UNDER_PERFORMING_RATIO_PERCENTAGE = 20;

    /**
     * Returns assoc array with
     * min, max, median and average values
     * from php array
     *
     * @param array $jsonArray
     * @return array
     */
    public function getMetricValues(array $jsonArray): array
    {
        $valueData = $this->parseValuesFromArray($jsonArray, 'metricValue');

        $min = $this->getMinValue($valueData);
        $max = $this->getMaxValue($valueData);
        $median = $this->getMedianValue($valueData);
        $average = $this->getAverageValue($valueData);

        return [
            'min' => $this->getBitValueFromByte($min),
            'max' => $this->getBitValueFromByte($max),
            'median' => $this->getBitValueFromByte($median),
            'average' => $this->getBitValueFromByte($average)
        ];
    }

    /**
     * @param array $jsonArray
     * @return array
     */
    public function getDateValues(array $jsonArray): array
    {
        $dateData = $this->parseValuesFromArray($jsonArray, 'dtime');

        return [
            'from' => $this->getMinValue($dateData),
            'to' => $this->getMaxValue($dateData),
        ];
    }

    public function getInvestigation(array $jsonArray): array
    {

        $underPerformingDates = $this->getUnderPerformingDates($jsonArray);
        if (empty($underPerformingDates)) {
            return [];
        }
        sort($underPerformingDates);

        return [
            'from' => $this->getMinValue($underPerformingDates),
            'to' =>   $this->getMaxValue($underPerformingDates),
        ];
    }

    private function getUnderPerformingDates(array $jsonArray): array
    {
        $values = $this->parseValuesFromArray($jsonArray, 'metricValue');

        $investigateValues = [];

        foreach ($values as $value) {

            if (
                $value <
                (
                    $this->getAverageValue($values)
                    * (1 - (self::UNDER_PERFORMING_RATIO_PERCENTAGE / 100))
                )
            ) {
                $investigateValues[] = $value;
            }

        }

        $investigateDates = [];
        foreach ($investigateValues as $investigateValue) {

            $res = false;
            foreach ($jsonArray['data'][0]['metricData'] as $n => $c) {
                if (in_array($investigateValue, $c)) {
                    $res = $n;
                    break;
                }
            }
            if ($res) {
                $investigateDates[] = $jsonArray['data'][0]['metricData'][$res]['dtime'];
            }

        }

        return $investigateDates;
    }

    /**
     * @param array $valueData
     * @return string
     */
    private function getMinValue(array $valueData): string
    {
        return (string)$valueData[0];
    }

    /**
     * @param array $valueData
     * @return string
     */
    private function getMaxValue(array $valueData): string
    {
        return (string)end($valueData);
    }

    /**
     * @param array $valueData
     * @return string
     */
    private function getMedianValue(array $valueData): string
    {

        $count = sizeof($valueData);
        $index = (int)floor($count / 2);

        if ($count & 1) {
            $median = $valueData[$index];
        } else {
            $median = ($valueData[$index - 1] + $valueData[$index]) / 2;
        }

        return (string)$median;
    }

    /**
     * @param array $valueData
     * @return string
     */
    private function getAverageValue(array $valueData): string
    {
        return (string)(array_sum($valueData) / count($valueData));

    }

    /**
     * @param array $array
     * @return array
     */
    private function parseValuesFromArray(array $array, string $subArrayKey, $sort = true): array
    {
        $values = [];

        foreach ($array['data'][0]['metricData'] as $elem) {
            $values[] = $elem[$subArrayKey];
        }

        if ($sort) {
            sort($values);
        }

        return $values;
    }

    /**
     * @param string $byteValue
     * @param int $precision
     * @return string
     */
    private function getBitValueFromByte(string $byteValue, int $precision = 2): string
    {
        return (string)round(((((float)$byteValue * 8) / 1000) / 1000), $precision);
    }
}