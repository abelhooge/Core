<?php

/**
 * Class EventPriority
 *
 * The EventPriority is an "enum" which gives priorities an integer value, the higher the integer value, the lower the
 * priority. The available priorities are, from highest to lowest:
 *
 * EventPriority::MONITOR
 * EventPriority::HIGHEST
 * EventPriority::HIGH
 * EventPriority::NORMAL
 * EventPriority::LOW
 * EventPriority::LOWEST
 *
 */

abstract class EventPriority
{

    const LOWEST    = 5;
    const LOW       = 4;
    const NORMAL    = 3;
    const HIGH      = 2;
    const HIGHEST   = 1;
    const MONITOR   = 0;

    /**
     * Returns the string of the priority based on the integer
     * @param $intPriorty
     * @return bool|string A bool when the integer isn't a priority. If the integer is a priority, the name is returned
     */
    static function getPriority($intPriorty){

        switch($intPriorty){

            case 5:
                return "EventPriority::LOWEST";
            case 4:
                return "EventPriority::LOW";
            case 3:
                return "EventPriority::NORMAL";
            case 2:
                return "EventPriority::HIGH";
            case 1:
                return "EventPriority::HIGHEST";
            case 0:
                return "EventPriority::MONITOR";
            default:
                return false;
        }
    }

    /**
     * Returns the highest priority
     * This function is needed for the firing of events in the right order,
     * @return int
     */
    static function getHighestPriority(){

        return EventPriority::MONITOR;
    }

    /**
     * Returns the lowest priority
     * This function is needed for the firing of events in the right order,
     * @return int
     */
    static function getLowestPriority(){

        return EventPriority::LOWEST;
    }
}