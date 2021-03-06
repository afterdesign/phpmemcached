<?php
require_once("phpmemcached/Autoload.php");

//Local virtualbox servers
$servers = array('10.0.1.103:11211', '10.0.1.104:11211');

// Create memcache instance and add servers
$memcache = new Memcache();
foreach($servers as $server) {
    $serverArray = explode(':', $server);
    $memcache->addServer($serverArray[0], $serverArray[1]);
}

//Just to proove it's working ;)
//set data with original memcache module
echo "----\n";
echo "Seting sha(x) for keys md5(x) where x>=0 and x<10 with PHP-MEMCACHE\n";
echo "----\n\n";
for($i=0; $i<1000; $i++) {
    $memcache->set(md5($i), sha1($i));
}


use phpmemcached\Protocols;
use phpmemcached\Algorithms;

//Create objects
$consistentHashingObject = new Algorithms\PhpMemcacheConsistentHashing($servers);
$memcachedProtocolObject = new Protocols\MemcachedBinaryProtocol();

for($i=0;$i<1000;$i++) {
    $serverAddress = $consistentHashingObject->findServer(md5($i));
    $dataFromServer = $memcachedProtocolObject->get(md5($i), $serverAddress);
    echo "----\n".$i." ".$serverAddress['host']."\n".$dataFromServer."\n";
}