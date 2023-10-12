<?php

use PHPUnit\Framework\TestCase;

class Omise_Payment_Base_Card_Test extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';
        // Mocking the parent class
        // $omisePayment = Mockery::mock('overload:Omise_Payment');
        // $omisePayment->shouldReceive('')
    }
}
