<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Queueable {
    /**
     * @var int
     */
    public $attempt = 0;

    /**
     * @var int
     */
    public $attempt_limit = 3;

    /**
     * @var int
     */
    public $adding_time = 5;

    public function schedule_single( $schedule_action, $data, $schedule_group ) {
        $schedule_time   = time() + $this->adding_time;
        $data['attempt'] = ++$this->attempt;
        WC()->queue()->schedule_single( $schedule_time, $schedule_action, $data, $schedule_group );
    }

    public function is_attempt_limit_exceeded() {
        return $this->attempt > $this->attempt_limit;
    }
}
