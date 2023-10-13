<?php

namespace WPRefers\NepaliDateWizard\Lib;

use Exception;

class NepaliDate
{

    private function getNeWeek( $year, $month, $day )
    {
        $julianDayCount = GregorianToJD( $month, $day, $year );
        return DataConfig::DAY_NE_ALIAS[ JDDayOfWeek($julianDayCount, 0) ];
    }

    private function validateNeDate( $year, $month, $day )
    {
        $message = null;

        if(!array_key_exists($year, DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH))
        {
            $message = 'Invalid Year Range';
        }
        if($month > 12 || $month < 1)
        {
            $message = 'Invalid Month Range';
        }
        if($day > DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$year][$month-1] || $day< 1)
        {
            $message = 'Invalid Day Range';
        }

        return [
            'err' => $message,
            'ok'  => is_null($message)
        ];
    }

    private function validateEnDate( $year, $month, $day )
    {
        $message = null;

        if ($year < DataConfig::EN_START_YEAR || $year > DataConfig::EN_END_YEAR) {
            $message = 'Invalid Year Range';
        }

        if ($month < 1 || $month > 12) {
            $message = 'Invalid Month Range';
        }

        if ($day < 1 || ( $day > cal_days_in_month(CAL_GREGORIAN, $month, $year) )) {
            $message = 'Invalid day Range';
        }

        return [
            'err' => $message,
            'ok'  => is_null($message)
        ];
    }

    public function convertAdToBs( $year, $month, $day )
    {
        $validate = $this->validateEnDate($year, $month, $day);

        if(!$validate['ok']) throw new Exception($validate['message']);

        try {
            $date = $year.'-'.$month.'-'.$day;
            $dayName = $this->getNeWeek($year, $month, $day);

            $startDate = date_create(DataConfig::EN_START_DAY);
            $today = date_create($date);

            $diff = date_diff($startDate, $today, true);

            $days = $diff->format("%a");

            $arr = '0';
            $mm = '';

            for ($i = DataConfig::NE_START_YEAR; $i < DataConfig::NE_END_YEAR; $i++)
            {
                $arr += array_sum(DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i]);

                if ($arr > $days)
                {
                    $year = $i;

                    $prevCount = $arr - array_sum(DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i]);
                    $prevYear = $i - 1;

                    for ($j = 0; $j < 12; $j++)
                    {
                        $prevCount += DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i][$j];
                        if($prevCount > $days)
                        {
                            $month = $j + 1;
                            $daysAlias = $prevCount - $days;
                            $currentDay = (DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i][$j] - $daysAlias) + 1;
                            break;
                        } elseif ($prevCount == $days)
                        {
                            $year = $i;
                            $month = $j+1;
                            $day = 1;
                        }
                    }
                    break;
                } elseif($arr == $days)
                {
                    $year = $i+1;
                    $month = 1;
                    $day = 1;
                }
            }
        } catch (Exception $e) {
            throw new Exception('Invalid Date Range');
        }

        return array('y' => $year, 'm' => $month, 'M' => DataConfig::MONTH_NE_ALIAS[$month - 1], 'd' => $currentDay, 'l' => $dayName);
    }

    public function convertBsToAd($year, $month, $day)
    {
        $validate = $this->validateNeDate($year, $month, $day);

        if(!$validate['ok']) throw new Exception($validate['message']);

        try {
            $startDate = date_create(DataConfig::EN_START_DAY);

            $dayCount = 0;
            $months = $month - 1;

            for($i = DataConfig::NE_START_YEAR; $i < $year; $i++)
            {
                $dayCount += array_sum(DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i]);
            }
            for($j = 0; $j < $months; $j++)
            {
                $dayCount += DataConfig::YEAR_MONTH_WISE_DAYS_LENGTH[$i][$j];
            }
            $dayCount += $day - 1;
        } catch (Exception $e) {
            throw new Exception('Invalid Date Range');
        }

        $nep = date_add($startDate, date_interval_create_from_date_string($dayCount." days"));

        return array('y' => date_format($nep, "Y"), 'm' => date_format($nep, "m"), 'M' => date_format($nep, "M"), 'd' => date_format($nep, "d"), 'l' => date_format($nep, "l"));
    }

}