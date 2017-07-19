<?php
define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once('../classes/FileCleaner.php');
require_once($CFG->libdir . '/clilib.php');
global $DB;
$time_start = microtime(true);
cli_heading("Clean orphan files cli-script");
cli_logo();
cli_writeln("");
cli_writeln("");
cli_writeln("See README.md for any info");
cli_writeln("");
cli_writeln("Script starts");
/*******************************************************************/
// now get cli options
list($options, $unrecognized) = cli_get_params(array('clean' => false, 'resetdate' => false),
    array('c' => 'clean', 'r' => 'resetdate'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
$fileCleaner = new FileCleaner();
if ($options['clean']) {
    $fileCleaner->clean(60 * 60 * 24);
} elseif ($options['resetdate']) {
    $fileCleaner->resetFileslastcleanup(time() - 60 * 60 * 24 * 365);
}
/*******************************************************************/
$time_end = microtime(true);
cli_writeln("");
cli_writeln('Script ends (' . (microtime(true) - $time_start) . ' seconds).');