<?php
declare(strict_types=1);
namespace App\Service;

final class StopWatch
{
    /**
     * @access private
     * @var int[]|int|float|false $startTime
     */
    private $startTime =  0;

    /**
     * @access private
     * @var int[]|int|float|false $endTime
     */
    private $endTime =  0;

    /**
     * @access private
     * @var int[]|int|float|false $elapsedTime
     */
    private $elapsedTime =  0;

    private $has_hr_present = false;

    public function __construct()
    {
        $this->endTime = 0;
        $this->startTime = 0;
        $this->elapsedTime = 0;
        $this->has_hr_present = (function_exists('hrtime'));
    }

    private function reset()
    {
        $this->endTime = 0;
        $this->startTime = 0;
        $this->elapsedTime = 0;
    }

    public function start()
    {
        if ($this->startTime > 0) {
            return false;
        }
        $this->reset();
        // int 64 bits
        // float 32 bits
        if ($this->has_hr_present) {
            $this->startTime =  hrtime(true);
        } else {
            // microsegundos a nano = hrtime
            $this->startTime =  microtime(true) * 1000000000;
        }
        return true;
    }

    public function stop()
    {
        if ($this->startTime == 0) {
            return false;
        }
        if ($this->has_hr_present) {
            $this->endTime =  hrtime(true);
        } else {
            // microsegundos a nano = hrtime
            $this->endTime =  microtime(true) * 1000000000;
        }
        $this->elapsedTime =  $this->endTime - $this->startTime;
    }

    public function getTimeElapsed(): string
    {
        if ($this->elapsedTime == 0) {
            return "";
        }
        /**
         * nano  = 1000000000
         * micro = 1000000
         * mili  = 1000
         * sec   = mili/1000
         * min   = sec / (60 * 1000))
         */
        $nanosec = $this->elapsedTime;
        $mlsec = ($nanosec / 1e+6);
        $seconds = (int)floor($mlsec / 1000);
        return strval($seconds) . 's';
    }
}
