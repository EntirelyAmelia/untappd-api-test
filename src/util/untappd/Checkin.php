<?php

namespace util\untappd;

/**
 * Object repsentation of checkin information to be outputted
 */
class Checkin
{
    public $id;

    public $beerName;

    public $breweryName;

    public $username;

    public $userFirstName;

    public $userLastName;

    public $userPhotoUrl;

    public $rating;

    public $comment;

    public $locationName;

    /**
     * Comparator for sorting Checkin objects by user's first name
     * @param  Checkin $a First object to compare
     * @param  Checkin $b Second object to compare
     * @return int Comparison result
     */
    public static function sortByFirstName($a, $b) {
        return strcmp($a->userFirstName, $b->userFirstName);
    }
}
