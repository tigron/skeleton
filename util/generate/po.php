<?php
/**
 * Generate .po files from templates.
 */

require_once dirname(__FILE__) . '/../../config/global.php';

// This script requires the skeleton-i18n package to be installed
if (!class_exists('\\Skeleton\\I18n\\Translation')) {
	echo 'You need to install the skeleton-i18n package first.' . "\n";
	exit(1);
}

require_once dirname(__FILE__) . '/../../lib/external/packages/tigron/skeleton-i18n/util/generate-po.php';
