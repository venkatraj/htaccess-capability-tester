<?php

namespace HtaccessCapabilityTester;

/**
 * Class for holding properties of a TestResult
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class TestResult
{

    /* @var bool|null   The result, null if inconclusive */
    public $status;

    /* @var string   Information about how the test failed / became inconclusive */
    public $info;

    /**
     * Constructor.
     *
     * @param  string  $status
     * @param  string  $info
     *
     * @return void
     */
    public function __construct($status, $info)
    {
        $this->status = $status;
        $this->info = $info;
    }
}