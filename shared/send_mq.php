<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('email sent', false, false, false, false);

$msg = new AMQPMessage('Email API!');
$channel->basic_publish($msg, '', 'email sent');

echo " [x] Message Sent '\n";

$channel->close();
$connection->close();

?>