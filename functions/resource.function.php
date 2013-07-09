<?php

function is_resource_of($resource, $resource_type) {
    if (!is_resource($resource)) return false;
    return (get_resource_type($resource) == $resource_type);
}

?>
