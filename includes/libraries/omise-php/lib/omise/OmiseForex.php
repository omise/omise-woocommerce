<?php

class OmiseForex extends OmiseApiResource
{
    public const ENDPOINT = 'forex';

    /**
     * Retrieves a forex data.
     *
     * @param  string $currency
     * @param  string $publickey
     * @param  string $secretkey
     *
     * @return OmiseForex
     */
    public static function retrieve($currency = '', $publickey = null, $secretkey = null)
    {
        return parent::g_retrieve(get_class(), self::getUrl($currency), $publickey, $secretkey);
    }

    /**
     * @see OmiseApiResource::g_reload()
     */
    public function reload()
    {
        parent::g_reload(self::getUrl($this['from']));
    }

    /**
     * @param  string $currency
     *
     * @return string
     */
    private static function getUrl($currency = '')
    {
        return OMISE_API_URL . self::ENDPOINT . '/' . $currency;
    }
}
