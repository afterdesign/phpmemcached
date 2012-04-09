<?php
namespace phpmemcached\Protocols;

/**
 * This is the main class for memcached binary protocol.
 * All information - http://memcached.org/
 * This is only proof of concept - it can be done.
 *
 * @author Malinowski
 */
class MemcachedBinaryProtocol {

    /**
     * http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped#Request_header
     * @var array
     */
    public $requestHeader = array(
        'magic' => 0x00,
        'opcode' => 0x00,
        'key_length' => 0x00,
        'extras_length' => 0x00,
        'data_type' => 0x00,
        'vbucket_id' => 0x00,
        'total_body_length' => 0x00,
        'opaque' => 0x00,
        'cas_part_1' => 0x00,
        'cas_part_2' => 0x00
    );

    /**
     * http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped#Response_header
     * @var array
     */
    public $responseHeader = array(
        'magic' => 0x00,
        'opcode' => 0x00,
        'key_length' => 0x00,
        'extras_length' => 0x00,
        'data_type' => 0x00,
        'status' => 0x00,
        'total_body_length' => 0x00,
        'opaque' => 0x00,
        'cas_part_1' => 0x00,
        'cas_part_2' => 0x00
    );

    /**
     * Data to quickly create a pack() string.
     * @var array
     */
    public $packRequestHeader = array(
        'magic' => 'C',
        'opcode' => 'C',
        'key_length' => 'n',
        'extras_length' => 'C',
        'data_type' => 'C',
        'vbucket_id' => 'n',
        'total_body_length' => 'N',
        'opaque' => 'I',
        'cas_part_1' => 'N',
        'cas_part_2' => 'N'
    );

    /**
     * Data to quickly create a unpack() string.
     * @var array
     */
    public $packResponseHeader = array(
        'magic' => 'C',
        'opcode' => 'C',
        'key_length' => 'n',
        'extras_length' => 'C',
        'data_type' => 'C',
        'status' => 'n',
        'total_body_length' => 'N',
        'opaque' => 'I',
        'cas_part_1' => 'N',
        'cas_part_2' => 'N'
    );

    /**
     * Header fields must be in correct order.
     * @var array
     */
    public $headerRequestFieldsList = array(
        'magic', 'opcode', 'key_length',
        'extras_length', 'data_type', 'vbucket_id',
        'total_body_length', 'opaque', 'cas_part_1', 'cas_part_2'
    );

    /**
     * Header fields must be in correct order.
     * @var array
     */
    public $headerResponseFieldsList = array(
        'magic', 'opcode', 'key_length',
        'extras_length', 'data_type', 'status',
        'total_body_length', 'opaque', 'cas_part_1', 'cas_part_2'
    );

    /**
     * Responses from server.
     * @var array
     */
    public $responseStatus = array(
        0x0000 => 'No error',
        0x0001 => 'Key not found',
        0x0002 => 'Key exists',
        0x0003 => 'Value too large',
        0x0004 => 'Invalid arguments',
        0x0005 => 'Item not stored',
        0x0006 => 'Incr/Decr on non-numeric value',
        0x0007 => 'The vbucket belongs to another server',
        0x0008 => 'Authentication error',
        0x0009 => 'Authentication continue',
        0x0081 => 'Unknown command',
        0x0082 => 'Out of memory',
        0x0083 => 'Not supported',
        0x0084 => 'Internal error',
        0x0085 => 'Busy',
        0x0086 => 'Temporary failure'
    );

    /**
     * No error in response
     * @var int
     */
    public $responseNoError = 0x0000;

    /**
     * http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped#Command_Opcodes
     * @var array
     */
    public $opcodes = array(
        'get' => 0x0,
        'set' => 0x1,
        'add' => 0x2,
        'replace' => 0x3,
        'delete' => 0x4,
        'increment' => 0x5,
        'decrement' => 0x6,
        'quit' => 0x7,
        'flush' => 0x8,
        'getq' => 0x9,
        'no-op' => 0xa,
        'version' => 0xb,
        'getk' => 0xc,
        'getkq' => 0xd,
        'append' => 0xe,
        'prepend' => 0xf,
        'stat' => 0x10,
        'setq' => 0x11,
        'addq' => 0x12,
        'replaceq' => 0x13,
        'deleteq' => 0x14,
        'incrementq' => 0x15,
        'decrementq' => 0x16,
        'quitq' => 0x17,
        'flushq' => 0x18,
        'appendq' => 0x19,
        'prependq' => 0x1a,
        'verbosity' => 0x1b,
        'touch' => 0x1c,
        'gat' => 0x1d,
        'gatq' => 0x1e,
        'sasl_list_mechs' => 0x20,
        'sasl_auth' => 0x21,
        'sasl_step' => 0x22,
        'rget' => 0x30,
        'rset' => 0x31,
        'rsetq' => 0x32,
        'rappend' => 0x33,
        'rappendq' => 0x34,
        'rprepend' => 0x35,
        'rprependq' => 0x36,
        'rdelete' => 0x37,
        'rdeleteq' => 0x38,
        'rincr' => 0x39,
        'rincrq' => 0x3a,
        'rdecr' => 0x3b,
        'rdecrq' => 0x3c,
        'set_vbucket' => 0x3d,
        'get_vbucket' => 0x3e,
        'del_vbucket' => 0x3f,
        'tap_connect' => 0x40,
        'tap_mutation' => 0x41,
        'tap_delete' => 0x42,
        'tap_flush' => 0x43,
        'tap_opaque' => 0x44,
        'tap_vbucket_set' => 0x45,
        'tap_checkpoint_start' => 0x46,
        'tap_checkpoint_end' => 0x47
    );

    /**
     * http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped#Magic_Byte
     * @var array
     */
    public $magic = array(
        'request' => 0x80,
        'response' => 0x81
    );

    public $rawBytes = 0x00;

    public $headerLength = 24;

    /**
     * This is the get method implementation.
     * http://code.google.com/p/memcached/wiki/BinaryProtocolRevamped#Get,_Get_Quietly,_Get_Key,_Get_Key_Quietly
     *
     * @param $key
     * @param $server
     * @return string
     * @author Malinowski
     */
    public function get($key, $server) {
        //we are making a request to server
        $this->requestHeader['magic'] = $this->magic['request'];

        //and we are trying to get some data
        $this->requestHeader['opcode'] = $this->opcodes['get'];

        //for get body and key length are the same
        $this->requestHeader['key_length'] = mb_strlen($key);
        $this->requestHeader['total_body_length'] =  mb_strlen($key);

        //header fields must be in order
        $stringPack = '';
        $bytesPack = array();
        foreach($this->headerRequestFieldsList as $field) {
            $stringPack .= $this->packRequestHeader[$field];
            $bytesPack[] = $this->requestHeader[$field];
        }

        //every letter must be in order and we need to add it's numeric value
        //and for every letter there must be a format character
        $keyArray = str_split($key);
        foreach($keyArray as $letter) {
            $stringPack = $stringPack."C";
            $bytesPack[] = ord($letter);
        }

        //add format string to bytesPack array
        array_unshift($bytesPack, $stringPack);

        //I hate this function but this is the easiest way to do this quickly
        $data = call_user_func_array("pack", $bytesPack);
        //create socket connection to memcached server
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, $server["host"], $server["port"]);

        //send prepared data
        socket_send($socket, $data, mb_strlen($data), MSG_EOF);

        //initialize variable for buffer
        $serverResponse = '';

        //read server response
        socket_recv($socket, $serverResponse, 4096, MSG_PEEK);
        return $this->getResponse($serverResponse);
    }

    /**
     * Just parse response from server based on data.
     *
     * @param $serverResponse
     * @return string
     * @throws Exception
     * @author Malinowski
     */
    protected function getResponse($serverResponse) {
        $stringUnPack = '';
        foreach($this->headerResponseFieldsList as $field) {
            $stringUnPack .= $this->packResponseHeader[$field].$field."/";
        }

        $unpackedData = unpack($stringUnPack, mb_substr($serverResponse, 0,$this->headerLength));

        //if error return error
        if($unpackedData['status'] !== $this->responseNoError) {
            throw new \Exception($this->responseStatus[$unpackedData['status']]);
        }

        $bodyLength = $unpackedData['total_body_length']-$unpackedData['extras_length'];

        $decodeStringArray = array_fill(0,$bodyLength,"C");
        $decodeString = "";
        foreach($decodeStringArray as $key=>$singleLetter) {
            $decodeString.=$singleLetter."a".$key."/";
        }

        $startFrom = $this->headerLength+$unpackedData['extras_length'];

        $data = unpack($decodeString, mb_substr($serverResponse, $startFrom, $bodyLength));

        $dataString = "";
        foreach($data as $letter){
            $dataString.=chr($letter);
        }

        return $dataString;
    }
}