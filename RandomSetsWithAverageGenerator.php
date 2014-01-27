<?php

function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function randomNumerInInterval($sum, $instance, $numberOfInstances, $interval, $machinesRatio)
{
  $rand = mt_rand(max($interval[0], $sum - ($numberOfInstances - $instance) * $interval[1]), min($interval[1], $sum - ($numberOfInstances - $instance) * $interval[0] + 1));
  $tmpSum = $sum - $rand;
  if ($tmpSum < ($numberOfInstances - $instance) * $interval[0]) {
    $tmpSum = randomNumerInInterval($sum, $instance, $numberOfInstances, $interval, $machinesRatio);
  } else if ($tmpSum > ($numberOfInstances - $instance) * $interval[1]) {
    $tmpSum = randomNumerInInterval($sum, $instance, $numberOfInstances, $interval, $machinesRatio);
  }
  return $tmpSum;
}

function randomTime($instance, $numberOfInstances, &$sums, $numberOfMachines, $interval, $machinesRatio)
{
  $result = [];
  for ($i=0; $i < $numberOfMachines; $i++) {
    $prevSum = $sums[$i];
    $sums[$i] = randomNumerInInterval($sums[$i], $instance + 1, $numberOfInstances, $interval, $machinesRatio);
    $result[] = $prevSum - $sums[$i];
  }
  return $result;
}

function randomTimes(&$sums, $numberOfInstances, $numberOfMachines, $interval, $machinesRatio)
{
  mt_srand(make_seed());
  $result = [];
  for ($i=0; $i < $numberOfInstances; $i++) { 
    $result[] = randomTime($i, $numberOfInstances, $sums, $numberOfMachines, $interval, $machinesRatio);
  }
  return $result;
}

$interval = [1, 120];
if ($interval[0] >= $interval[1] || $interval[0] <= 0 || $interval[1] <= 0) exit('Bad interval');
$numberOfInstances = 25;
if (0 >= count($numberOfInstances)) exit('Bad count of instances');
$numberOfMachines = 3;
$machinesRatio = [3, 1, 2];
if ($numberOfMachines <= 0 || $numberOfMachines !== count($machinesRatio)) exit('Bad count of macines or ratio');

$maxMinMachineTime = $interval[1] * min($machinesRatio) / max($machinesRatio);
$minMinMachineTime = $interval[0];
$randMinMachine = mt_rand($numberOfInstances * $minMinMachineTime, $numberOfInstances * $maxMinMachineTime);
$sums = [];
foreach ($machinesRatio as $ratio) {
  $sums[] = ceil($randMinMachine * ($ratio / min($machinesRatio)));
}
$randomTimes = randomTimes($sums, $numberOfInstances, $numberOfMachines, $interval, $machinesRatio);

$first = 0;
$second = 0;
$third = 0;
// $forth = 0;
foreach ($randomTimes as $random) {
  $first += $random[0];
  $second += $random[1];
  $third += $random[2];
  // $forth += $random[3];
}
$suma = $first + $second + $third; //+ $forth;
var_dump($first / $suma, $second / $suma, $third / $suma);//, $forth / $suma);

if($numberOfInstances !== count($randomTimes)) exit('Didn\'t generate random times for all instances');