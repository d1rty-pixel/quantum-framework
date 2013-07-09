<?php

namespace core\scriptlet;

uses ("core.base.Quobject");

final class Scriptlet extends \core\base\Quobject {

	private static $instance       = null;
    private static $protocol       = null;
    private static $content_type   = null;

	public static function factory($protocol = null, $content_type = null) {
		if (!is_null(self::$instance)) {
			return self::$instance;
		}

        self::$protocol     = $protocol;
        self::$content_type = $content_type;

		if (is_null(self::$protocol))
			throw new \core\exceptions\IllegalArgumentException("Protocol must be defined to create scriptlet");

        if (is_null(self::$content_type))
			throw new \core\exceptions\IllegalArgumentException("Content-type must be defined to create scriptlet");

		$protocol_scriptlet_name = sprintf('%sScriptlet', ucfirst($protocol));
		$content_scriptlet_name  = sprintf('%sScriptlet', ucfirst($content_type));

        uses ("core.scriptlet.".$protocol_scriptlet_name, "core.scriptlet.".$content_scriptlet_name);

        trace("Creating instance of $protocol_scriptlet_name with content-type scriptlet $content_scriptlet_name", "Scriptlet");
        $protocol_scriptlet_class_name = sprintf('\core\scriptlet\%s', $protocol_scriptlet_name);
        $content_scriptlet_class_name = sprintf('\core\scriptlet\%s', $content_scriptlet_name);
		self::$instance = new $protocol_scriptlet_class_name($content_scriptlet_class_name);

		return self::$instance;
	}
}

?>
