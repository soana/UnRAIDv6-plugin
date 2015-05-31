<?PHP
/* Copyright 2015, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * Plugin development contribution by gfjardim
 */
?>
<?
function scan_dir($dir, $type = "") {
  $out = array();
  foreach (array_slice(scandir($dir), 2) as $entry){
    $sep   = (preg_match("/\/$/", $dir)) ? "" : "/";
    $out[] = $dir.$sep.$entry ;
  }
  return $out;
}

function list_fan() {
  $out = array();
  foreach (scan_dir('/sys/class/hwmon/') as $chip) {
    $name = is_file($chip."/name") ? file_get_contents($chip."/name") : "";
    foreach (preg_grep("/fan\d+_input/", scan_dir($chip)) as $fan) {
      $out[] = array('chip'=>$name, 'name'=>end(split('/',$fan)), 'sensor'=>$fan , 'rpm'=>file_get_contents($fan));
    }
  }
  return $out;
}

switch ($_GET['op']) {
case 'detect':
if (is_file( $_GET['pwm'])) {
  $pwm = $_GET['pwm'];
  $default_method = file_get_contents($pwm."_enable");
  $default_rpm    = file_get_contents($pwm);
  file_put_contents($pwm."_enable", "1");
  file_put_contents($pwm, "150");
  sleep(3);
  $init_fans = list_fan();
  file_put_contents($pwm, "255");
  sleep(3);
  $final_fans = list_fan();
  file_put_contents($pwm, $default_rpm);
  file_put_contents($pwm."_enable", $default_method);
  for ($i=0; $i < count($final_fans); $i++) {
    if (($final_fans[$i]['rpm'] - $init_fans[$i]['rpm'])>10) {
      echo $init_fans[$i]['sensor'];
      break;
    }
  }
}
break;
case 'pwm':
if (is_file( $_GET['pwm']) && is_file( $_GET['fan'])) {
  exec("/etc/rc.d/rc.autofan stop >/dev/null");
  $pwm     = $_GET['pwm'];
  $fan     = $_GET['fan'];
  $fan_min = split("_", $_GET['fan'])[0]."_min";
  $default_method  = file_get_contents($pwm."_enable");
  $default_pwm     = file_get_contents($pwm);
  $default_fan_min = file_get_contents($fan_min);
  file_put_contents($pwm."_enable", "1");
  file_put_contents($fan_min, "0");
  file_put_contents($pwm, "0");
  sleep(5);
  $min_rpm = file_get_contents($fan);
  foreach (range(0, 20) as $i) {
    $val=$i*5;
    file_put_contents($pwm, $val);
    sleep(1);
    if ((file_get_contents($fan) - $min_rpm)>10) {
      sleep(10);
      if ((file_get_contents($fan) - $min_rpm)>10) {
        echo $val;
        break;
      }
    }
  }
  file_put_contents($pwm, $default_pwm);
  file_put_contents($fan_min, $default_fan_min);
  file_put_contents($pwm."_enable", $default_method);
  exec("/etc/rc.d/rc.autofan start >/dev/null");
}
break;}
?>
