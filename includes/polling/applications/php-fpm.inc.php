<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'php-fpm';
$app_id = $app['app_id'];

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.112.104.112.102.112.109.115.112';
$phpfpm = snmp_walk($device, $oid, $options, $mib);

list($pool,$start_time,$start_since,$accepted_conn,$listen_queue,$max_listen_queue,$listen_queue_len,$idle_processes,
     $active_processes,$total_processes,$max_active_processes,$max_children_reached,$slow_requests) = explode("\n", $phpfpm);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('lq', 'GAUGE', 0)
    ->addDataset('mlq', 'GAUGE', 0)
    ->addDataset('ip', 'GAUGE', 0)
    ->addDataset('ap', 'GAUGE', 0)
    ->addDataset('tp', 'GAUGE', 0)
    ->addDataset('map', 'GAUGE', 0)
    ->addDataset('mcr', 'GAUGE', 0)
    ->addDataset('sr', 'GAUGE', 0);

$fields = array(
    'lq' => $listen_queue,
    'mlq' => $max_listen_queue,
    'ip' => $idle_processes,
    'ap' => $active_processes,
    'tp' => $total_processes,
    'map' => $max_active_processes,
    'mcr' => $max_children_reached,
    'sr' => $slow_requests
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
