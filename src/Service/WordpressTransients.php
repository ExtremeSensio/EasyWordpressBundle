<?php

namespace EasyWordpressBundle\Service;

/**
 * Class WordpressTransients
 *
 * @package EasyWordpressBundle\Service
 */

class WordpressTransients
{
    /**
     * Expiration time in seconds
     */
    const MINUTE_IN_SECONDS = 60;
    const HOURS_IN_SECONDS = 60 * MINUTE_IN_SECONDS;
    const DAY_IN_SECONDS = 60 * HOUR_IN_SECONDS;
    const WEEK_IN_SECONDS = 60 * DAY_IN_SECONDS;
    const MONTH_IN_SECONDS = 60 * DAY_IN_SECONDS;
    const YEAR_IN_SECONDS = 60 * MONTH_IN_SECONDS;

    /**
     * @param $transientName
     *
     * @return bool|mixed
     */
    public function getTransient($transientName)
    {
        if (false === ($data = get_transient($transientName))) {
            return false;
        }

        return get_transient($transientName);
    }


    /**
     * Creates the transient
     *
     * @param string $transientName The transient name
     * @param string|array $value The transient value
     * @param int $expiration Expiration in seconds
     */
    public function setTransient(
        $transientName,
        $value,
        $expiration = 1 * self::MINUTE_IN_SECONDS
    ) {
        set_transient($transientName, $value, $expiration);
    }

    /**
     * Removes a transient
     *
     * @param string $transientName The transient name
     */
    public function deleteTransient($transientName)
    {
        delete_transient($transientName);
    }
}
