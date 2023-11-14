<?php

require_once __DIR__ . '/bootstrap-test-setup.php';

abstract class Omise_Payment_Offline_Test extends Bootstrap_Test_Setup
{
    public function setUp(): void
    {
        parent::setUp();
        Mockery::mock('alias:Omise_Payment')->makePartial();
    }
}
