<?php

function isPrivateIP($ip) {
    foreach (array("10.0.0.0/8", "172.16.0.0/12", "192.168.0.0/16") as $subnet) {
        list($net, $mask) = explode('/', $subnet);
        if (isIPInSubnet($ip, $net, $mask)) {
            return true;
        }
    }
    return false;
}

function isIPInSubnet($ip, $net, $mask) {
    $firstpart = substr(str_pad(decbin(ip2long($net)), 32, "0", STR_PAD_LEFT) ,0 , $mask);
    $firstip = substr(str_pad(decbin(ip2long($ip)), 32, "0", STR_PAD_LEFT), 0, $mask);

    return (strcmp($firstpart, $firstip) == 0);
}

?>
