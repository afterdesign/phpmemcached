<?php
namespace MyMemcached;
include "MyCrc.php";

/**
 * Class with consistent hashing algorithm.
 * This variation is taken from php-memcache module of php - http://pecl.php.net/memcache
 * Algorithm is from version 3.0.x (which is beta)
 * You can install it on debian with apt-get install php-memcache
 * This class does not store array of current servers.
 * This is only proof of concept - it can be done.
 *
 * @author Malinowski
 */
class ConsistentHashing {

    /**
     * Constructor with default values.
     *
     * @param array $nodes
     * @param int $consistent_points
     * @param int $consistent_buckets
     * @param int $weight
     * @author Malinowski
     */
    public function __construct($nodes=array(), $consistent_points = 160, $consistent_buckets = 1024, $weight = 1) {
        $this->serversList = $nodes;
        $this->weight = $weight;
        $this->consistentPoints = $consistent_points;
        $this->consistentBuckets = $consistent_buckets;
        
        $this->points = array();
        $this->sortedPointsKeys = null;
        
        $this->buckets = array();

        /**
         * For each server node there must be created some data
         */
        foreach($nodes as $server) {
            $this->__addServer($server);
        }

        // after creating points for each server create an array
        $this->__populateBuckets();
    }

    /**
     * Create an array of values
     * array[bucketNumber] -> ServerAddress
     * @author Malinowski
     */
    private function __populateBuckets() {
        $this->sortedPointsKeys = array_keys($this->points);
        sort($this->sortedPointsKeys);
        
        $pointSearch = 0xffffffff / $this->consistentBuckets;

        for($bucketNumber=0; $bucketNumber < $this->consistentBuckets; $bucketNumber++) {
            $this->buckets[$bucketNumber] = $this->__consistentFind($pointSearch * $bucketNumber);
        }
    }

    /**
     * Binary search of server on map created by adding servers
     *
     * @param $pointSearch
     * @return mixed
     * @author Malinowski
     */
    private function __consistentFind($pointSearch) {
        $pointStart = 0;
        $pointLast = count($this->points) - 1;

        //search if any point is exactly what we are searching for
        $testSearch = array_search($pointSearch, $this->sortedPointsKeys);
        if($testSearch !== false) {
            return $testSearch;
        }

        //Check if value we are looking for is not outside the range
        if($pointSearch <= $this->sortedPointsKeys[0] || $pointSearch > $this->sortedPointsKeys[$pointLast]) {
            return $this->points[$this->sortedPointsKeys[0]];
        }

        //simple binary search for point in the middle of our value
        while(True) {
            $pointMiddle = floor($pointStart + ($pointLast-$pointStart)/(float)2);
            
            if($pointSearch > $this->sortedPointsKeys[$pointMiddle]) {
                $pointStart = $pointMiddle + 1;
            }
            else {
                $pointLast = $pointMiddle - 1;
            }

            if($this->sortedPointsKeys[$pointMiddle - 1] < $pointSearch && $pointSearch <= $this->sortedPointsKeys[$pointMiddle]) {
                return $this->points[$this->sortedPointsKeys[$pointMiddle]];
            }
        }
    }

    /**
     * Add server.
     * This method is creating points for every server.
     * server:port string is concatenated with "-" and I don't know why ;)
     *
     * @param string $serverData
     * @author Malinowski
     */
    private function __addServer($serverData) {
        $pointsLength = $this->weight * $this->consistentPoints;

        //Yup. That is strange.
        $serverHash = crc32($serverData.'-');

        for($singlePoint=0; $singlePoint < $pointsLength; $singlePoint++) {
            $point = MyCrc::crc32($singlePoint, $serverHash);
            $this->points[$point] = $serverData;
        }
    }

    /**
     * Public method to add another server.
     *
     * @param $serverData
     * @author Malinowski
     */
    public function addServer($serverData) {    
        if(is_array($serverData)) {
            foreach ($serverData as $server) {

                $this->__addServer($server);
            }
        }
        else {
            $this->__addServer($serverData);
        }

        $this->__populateBuckets();
    }

    /**
     * Find server for a given key.
     *
     * @param string $key
     * @return array
     * @author Malinowski
     */
    public function findServer($key) {
        if(count($this->serversList) == 1) {
            return $this->serversList[0];
        }

        //because php have some issues with crc32
        //also you can use bindec(decbin($key))
        $hash = sprintf("%u",crc32($key));

        $serverData = explode(":",$this->buckets[fmod($hash, $this->consistentBuckets)]);
        return array("host" => $serverData[0], "port" => $serverData[1]);
    }
}