<?php

/**
 * uses function - use the bitch!
 *
 * This function includes classes only once, you can "use" them as often as you want.
 *
 * We differ between three types of classes, the "core", "app" and "plugin" classes:
 *  - core classes come from QUANTUM_ROOT/class
 *  - app classes come from APP_ROOT/class
 *  - plugin classes reside in QUANTUM_ROOT/plugins/class
 *
 * A complete list of all core classes is available at:
 * http://www.neogoth.org/quantum-framework/core/overview#classes
 *
 * This is a modified uses function originally from the XP-Framework.
 */

function uses() {
    $args = func_get_args();

    if (is_array($args[0]) && count($args) == 1 ) {
        $args = $args[0];
    }

    $class_type_mapping = array(
        "core"  => array(
            "path"      => "%s/class/%s%s%s",
            "begin"     => QUANTUM_ROOT,
            "end"       => ".class.php",
        ),
        "addon" => array(
            "path"      => "%s/class/addons/%s/class/%s%s",
            "begin"     => QUANTUM_ROOT,
            "end"       => ".class.php",
        ),
        "app"   => array(
            "path"      => "%s/class/%s%s%s",
            "begin"     => APP_ROOT,
            "end"       => ".class.php",
        ),
    );

    $classes = array_diff($args, $GLOBALS['uses_cache']);

    foreach ($classes as $class) {
        $path_nodes = explode(".", $class);
		$class_type = $path_nodes[0];

        if (!in_array($class_type, array_keys($class_type_mapping))) {
            throw new \core\exceptions\IllegalArgumentException(sprintf("Class type '%s' not recognized in '%s'", $class_type, $class));
        }
        $mapping = $class_type_mapping[$class_type];

        $path_middle = array();
        for ($i = 1; $i <= count($path_nodes) - 2; $i++) {
            array_push($path_middle, $path_nodes[$i]);
        }
        $path_end = $path_nodes[count($path_nodes) - 1];

        $path = sprintf($mapping["path"], $mapping["begin"], implode("/", $path_middle), sprintf("/%s", $path_end), $mapping["end"]);

        if (file_exists($path)) {
            $GLOBALS['uses_cache'][] = $class;
            require_once($path);
            continue;
        } else {
            print "file $path not found\n";
            throw new \core\exceptions\ClassNotFoundException(sprintf("File '%s' does not exists", $path));
        }
    }
}

$GLOBALS['uses_cache'] = array();

uses (
    "core.exceptions.ClassNotFoundException",
    "core.exceptions.IllegalArgumentException"
);

?>
