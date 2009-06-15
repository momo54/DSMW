<?php
# The system determines which wiki to display based on either:
#   W parameter passed to INDEX.PHP, or
#   the cookie "wikiCode"
#
# First, test for the W parameter. Was it passed?
$sr_WikiCode = $_REQUEST['w'];
# Tip: this new variable is prefixed with my initials "sr_" in order
#      to distinguish it from the variables that are standard within MediaWiki
if ($sr_WikiCode <> "") {
   # Yes, W parameter was passed, so save it in a cookie until it gets changed.
   # 2008-04-18 Note: MediaWiki sets its cookies using variables, like the following:
   #   setcookie($name,$value,$expire, $wgCookiePath, $wgCookieDomain, $wgCookieSecure);
   # I haven't yet tested it using their variables, so I've hard-coded it:
   setcookie('wikiCode', $sr_WikiCode, time()+60*60*24*365, '/', '.yourdomain.com');
} elseif ($_COOKIE['wikiCode'] <> "") {
   # the parameter "W" wasn't passed but the cookie wikiCode does have a value, so use it
   $sr_WikiCode = $_COOKIE['wikiCode'];
} else {
   # neither the W parameter was passed nor does the cookie wikiCode have a value, so
   # we don't know which wiki to display. Let user know that we cannot continue.
   die('No valid wiki specified');
}
# We know which wiki to display. Set the configuration variables specific
# to the individual wiki
require_once ('LocalSettings_' . $sr_WikiCode . '.php');
?>
