<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'OmiseCustomerCard' ) ) {
	class OmiseCustomerCard
    {
        /**
         * @var OmiseCustomerCard
         */
        private $customer;

        public function __construct()
        {
            $this->customer = new Omise_Customer;
        }

        public function get($customerId)
        {
            $customer = $this->customer->get($customerId);
            return $customer->cards( array( 'order' => 'reverse_chronological' ) );
        }

        public function delete($cardId, $customerId)
        {
            $customer = $this->customer->get($customerId);
			$card = $customer->cards()->retrieve($cardId);
			$card->destroy();
            return $card->isDestroyed();
        }

        /**
         * Adding a card to a customer
         * @param string $token
         * @param string $customerId
         * 
         * @return string
         */
        public function create($customerId, $token)
        {
            $customer = $this->customer->get($customerId);
            $customer->update( ['card' => $token ]);

            $cards = $customer->cards([
                'limit' => 1,
                'order' => 'reverse_chronological'
            ]);

            return $cards['data'][0]; // card ID
        }
    }
}
