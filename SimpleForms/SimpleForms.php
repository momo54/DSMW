<?php
/**
 * SimpleForms extension - Provides functions to make and process forms
 *
 * See http://www.mediawiki.org/Extension:Simple_Forms for installation and usage details
 * See http://www.organicdesign.co.nz/Extension_talk:SimpleForms.php for development notes and disucssion
 *
 * Started: 2007-04-25, see article history
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Aran Dunkley [http://www.organicdesign.co.nz/nad User:Nad]
 * @copyright © 2007 Aran Dunkley
 * @licence GNU General Public Licence 2.0 or later
 */

if (!defined('MEDIAWIKI')) die('Not an entry point.');
define('SIMPLEFORMS_VERSION', '0.4.11, 2009-01-14');

# index.php parameter names
define('SIMPLEFORMS_CONTENT',  'content');   # used for parsing wikitext content
define('SIMPLEFORMS_CACTION',  'caction');   # specify whether to prepend, append or replace existing content
define('SIMPLEFORMS_SUMMARY',  'summary');   # specify an edit summary when updating or creating content
define('SIMPLEFORMS_PAGENAME', 'pagename');  # specify a page heading to use when rendering content with no title
define('SIMPLEFORMS_MINOR',    'minor');     # specify that the edit/create be flagged as a minor edit
define('SIMPLEFORMS_TACTION',  'templates'); # specify that the edit/create be flagged as a minor edit
define('SIMPLEFORMS_USERNAME', 'username');  # specify a different username to use when the server is editing
define('SIMPLEFORMS_RETURN',   'returnto');  # specify a page to return to after processing the request
define('SIMPLEFORMS_REGEXP',   'regexp');    # if the content-action is replace, a perl regular expression can be used

# Parser function names
$wgSimpleFormsFormMagic     = "form";        # the parser-function name for form containers
$wgSimpleFormsInputMagic    = "input";       # the parser-function name for form inputs
$wgSimpleFormsRequestMagic  = "request";     # the parser-function name for accessing the post/get variables
$wgSimpleFormsParaTypeMagic = "paratype";    # the parser-function name for checking post/get parameter types

# Configuration
$wgSimpleFormsRequestPrefix = "";            # restricts #request and GET/POST variable names to their own namespace, set to "" to disable
$wgSimpleFormsServerUser    = "";            # Set this to an existing username so server IP doesn't show up in changes
$wgSimpleFormsAllowCreate   = true;          # Allow creating new articles from content query item
$wgSimpleFormsAllowEdit     = true;          # Allow appending, prepending or replacing of content in existing articles from query item

# Allow anonymous edits from these addresses
$wgSimpleFormsAllowRemoteAddr = array('127.0.0.1');
if (isset($_SERVER['SERVER_ADDR'])) $wgSimpleFormsAllowRemoteAddr[] = $_SERVER['SERVER_ADDR'];

$wgSimpleFormsEnableCaching = true;
define('SIMPLEFORMS_UNTITLED', 'UNTITLED');
define('SFEB_NAME',   0);
define('SFEB_OFFSET', 1);
define('SFEB_LENGTH', 2);
define('SFEB_DEPTH',  3);

$wgExtensionFunctions[]		= 'wfSetupSimpleForms';
$wgHooks['LanguageGetMagic'][] = 'wfSimpleFormsLanguageGetMagic';

$wgExtensionCredits['parserhook'][] = array(
	'name'        => 'Simple Forms',
	'author'      => '[http://www.organicdesign.co.nz/nad User:Nad]',
	'description' => 'Functions to make and process forms.',
	'url'         => 'http://www.mediawiki.org/wiki/Extension:Simple_Forms',
	'version'     => SIMPLEFORMS_VERSION
);

# If it's a simple-forms ajax call, don't use dispatcher
if ($wgUseAjax && isset($_REQUEST['action']) && $_REQUEST['action'] == 'ajax' && $_REQUEST['rs'] == 'wfSimpleFormsAjax') {
	$_REQUEST['action'] = 'render';
	if (is_array($_REQUEST['rsargs']))
		foreach ($_REQUEST['rsargs'] as $arg)
			if (preg_match('/^(.+?)=(.+)$/', $arg, $m))
				$_REQUEST[$m[1]] = $m[2];
}

# todo: handle action=edit by making $_REQUEST['preload']='UNTITLED' and still add the AAFC hook
#	   handle action=raw by changing action to render and adding SimpleForms::raw to an appropriate hook

# If there is content passed in the request but no title, set title to the dummy "UNTITLED" article, and add a hook to replace the content
# - there's probably a better way to do this, but this will do for now
if (isset($_REQUEST[SIMPLEFORMS_CONTENT]) && !isset($_REQUEST['title'])) {
	$wgHooks['ArticleAfterFetchContent'][] = 'wfSimpleFormsUntitledContent';
	$_REQUEST['title'] = SIMPLEFORMS_UNTITLED;
	$wgSimpleFormsEnableCaching = false;
}

function wfSimpleFormsUntitledContent(&$article, &$text) {
	global $wgOut, $wgRequest;
	if ($article->getTitle()->getText() == SIMPLEFORMS_UNTITLED) {
		$text = $wgRequest->getText(SIMPLEFORMS_CONTENT);
		if ($title = $wgRequest->getText(SIMPLEFORMS_PAGENAME)) $wgOut->setPageTitle($title);
		else {
			$wgOut->setPageTitle(' ');
			$wgOut->addScript('<style>h1.firstHeading{display:none}</style>');
		}
	}
	return true;
}

# If the request originates locally, auto-authenticate the user to the server-user
$wgHooks['AutoAuthenticate'][] = 'wfSimpleFormsAutoAuthenticate';
function wfSimpleFormsAutoAuthenticate(&$user) {
	global $wgRequest, $wgSimpleFormsServerUser, $wgSimpleFormsAllowRemoteAddr;
	if ($username = $wgRequest->getText(SIMPLEFORMS_USERNAME)) $wgSimpleFormsServerUser = $username;
	if (!empty($wgSimpleFormsServerUser) && in_array($_SERVER['REMOTE_ADDR'], $wgSimpleFormsAllowRemoteAddr))
		$user = User::newFromName($wgSimpleFormsServerUser);
	return true;
}

/**
 * Define a singleton for SimpleForms operations
 */
class SimpleForms {

	var $id = 0;

	/**
	 * Constructor
	 */
	function SimpleForms() {
		global $wgParser, $wgHooks, $wgTitle, $wgSimpleFormsFormMagic, $wgSimpleFormsInputMagic,
			$wgSimpleFormsRequestMagic, $wgSimpleFormsParaTypeMagic, $wgSimpleFormsEnableCaching;
		$wgParser->setFunctionHook($wgSimpleFormsFormMagic,     array($this,'formMagic'));
		$wgParser->setFunctionHook($wgSimpleFormsInputMagic,    array($this,'inputMagic'));
		$wgParser->setFunctionHook($wgSimpleFormsRequestMagic,  array($this,'requestMagic'));
		$wgParser->setFunctionHook($wgSimpleFormsParaTypeMagic, array($this,'paramTypeMagic'));
		$this->createUntitled();
		$this->processRequest();
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'render' && (!is_object($wgTitle) || isset($_REQUEST['content']))) {
			$wgHooks['OutputPageBeforeHTML'][] = array($this, 'render');
			$wgSimpleFormsEnableCaching = false;
			}
		$this->id = uniqid('sf-');
		}

	/**
	 * Renders a form and wraps it in tags for processing by tagHook
	 * - if it's an edit-form it will return empty-string unless $this->edit is true
	 * i.e. $this->edit would be set by the edit-hook or create-specialpage parsing it
	 */
	function formMagic(&$parser) {
		global $wgScript, $wgSimpleFormsEnableCaching;
		if (!$wgSimpleFormsEnableCaching) $parser->disableCache();
		$argv = func_get_args();
		$id = $this->id;
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'render' && isset($_REQUEST['wiklet']))
			$hidden = '<input type="hidden" name="action" value="render"/>
			<input type="hidden" name="wiklet"/>';
		else $hidden = '';
		unset($argv['action']);
		$form = '';
		$args = '';
		$argl = array();
		foreach ($argv as $arg) if (!is_object($arg)) {
			if (preg_match('/^([a-z0-9_]+?)\\s*=\\s*(.+)$/is', $arg, $match)) {
				$args .= " $match[1]=\"$match[2]\"";
				$argl[$match[1]] = $match[2];
			} else $form = $arg;
		}
		$action = isset($argl['action']) ? $argl['action'] : $wgScript;
		$form = "<form$args action=\"$action\" id=\"$id\">$hidden$form</form>";
		$this->id = uniqid('sf-');
		$form = preg_replace("/^\\s+/m",'',$form);
		return array($form, 'noparse' => true, 'isHTML' => true);
	}

	/**
	 * Renders a form input
	 */
	function inputMagic(&$parser) {
		global $wgSimpleFormsRequestPrefix, $wgSimpleFormsEnableCaching;
		if (!$wgSimpleFormsEnableCaching) $parser->disableCache();

		$content = '';
		$method  = '';
		$type    = '';
		$args    = '';
		$argv    = array();

		# Process args
		foreach (func_get_args() as $arg) if (!is_object($arg)) {
			if (preg_match('/^([a-z0-9_]+?)\\s*=\\s*(.+)$/is', $arg, $match)) $argv[trim($match[1])] = trim($match[2]);
			else $content = trim($arg);
		}
		if (isset($argv['type'])) $type = $argv['type']; else $type = '';
		if (isset($argv['name'])) $argv['name'] = $wgSimpleFormsRequestPrefix.$argv['name'];

		# Textarea
		if ($type == 'textarea') {
			unset($argv['type']);
			foreach ($argv as $k => $v) $args .= " $k=\"$v\"";
			$input = "<textarea$args>$content</textarea>";
		}

		# Select list
		elseif ($type == 'select' ) {
			unset($argv['type']);

			if (isset($argv['multiple'])) {
				if (isset($argv['name'])) $argv['name'] .= '[]';
			}

			if (isset($argv['value'])) {
				$val = $argv['value'];
				unset($argv['value']);
			} else $val = '';

			foreach ($argv as $k => $v) $args .= " $k=\"$v\"";

			preg_match_all( '/^\\*\\s*(.*?)\\s*$/m', $content, $m );
			$input = "<select$args>\n";
			foreach ($m[1] as $opt ) {
				$sel = $opt == $val ? ' selected' : '';
				$input .= "<option$sel>$opt</option>\n";
			}

			$input .= "</select>\n";
		}

		# Ajax link or button
		elseif ($type == 'ajax') {
			$update = isset($argv['update']) ? $argv['update'] : $this->id;
			$format = isset($argv['format']) ? $argv['format'] : 'button';
			unset($argv['update']);
			unset($argv['format']);
			if (isset($argv['template'])) {
				$template = '{'.'{'.$argv['template'];
				$template = "var t = '$template\\n';
					inputs = f.getElementsByTagName('select');
					for (i = 0; i < inputs.length; i++)
						if (n = inputs[i].getAttribute('name'))
							t += '|' + n + '=' + inputs[i].getAttribute('selected') + '\\n';
					t = t + '}'+'}\\n';
					alert(t);/*
					i = document.createElement('input');
					i.setAttribute('type','hidden');
					i.setAttribute('name','templates');
					i.setAttribute('value','update');
					f.appendChild(i);
					i = document.createElement('input');
					i.setAttribute('type','hidden');
					i.setAttribute('name','content');
					i.setAttribute('value',t);
					f.appendChild(i);*/";
				unset($argv['template']);
			} else $template = '';

			if ($format == 'link') {
				# Render the Ajax input as a link independent of any form
				$element = 'a';
				$t = isset($argv['title']) ? $argv['title'] : false;
				if ($content == '') $content = $t;
				if ($t) $t = Title::newFromText($t);
				$argv['class'] = !$t || $t->exists() ? 'ajax' : 'new ajax';
				unset($argv['type']);
				$params = array();
				foreach ($argv as $k => $v) if ($k != 'class') $params[] = "'$k=$v'";
				$params = join(',',$params);
				$argv['href'] = "javascript:var x = sajax_do_call('wfSimpleFormsAjax',[$params],document.getElementById('$update'))";
			}
			else {
				# Render the Ajax input as a form submit button
				$argv['type'] = 'button';
				$element	  = 'input';
				if (!isset($argv['onClick'])) $argv['onClick'] = '';
				$argv['onClick'] .= "a = [];
					f = document.getElementById('{$this->id}');
					i = f.elements;
					for (var k = 0; k < f.elements.length; k++) {
					  if (i[k].type == 'select-one') {
						if (i[k].selectedIndex !== undefined ) {
						  a.push(i[k].name+'='+i[k].options[i[k].selectedIndex].text);
						}
					  } else if (i[k].name && i[k].value &&
						  (i[k].type != 'radio' || i[k].checked) &&
						  (i[k].type != 'checkbox' || i[k].checked)) {
							 a.push(i[k].name+'='+i[k].value);
					  }
					}
					sajax_request_type = 'POST';
					x = sajax_do_call('wfSimpleFormsAjax',a,document.getElementById('$update'))";
			}

			foreach ($argv as $k => $v) $args .= " $k=\"$v\"";
			$input = "<$element$args>$content</$element>\n";
		}

		# Default: render as normal input element
		else {
			foreach ($argv as $k => $v) $args .= " $k=\"$v\"";
			$input = "<input$args/>";
		}

		$input = preg_replace("/^\\s+/m",'',$input);
		return array($input,'noparse' => true, 'isHTML' => true);
		}

	/**
	 * Return value from the global $_REQUEST array (containing GET/POST variables)
	 */
	function requestMagic(&$parser) {
		global $wgRequest, $wgSimpleFormsRequestPrefix, $wgContLang;

		$args = func_get_args();

		# the first arg is the parser.  We already have it (by
		# reference even), so we can remove it from the array
		array_shift($args);

		# get the request parameter name
		$paramName = array_shift($args);

		# only thing left in $args at this point are the array keys
		# If no keys are specified, we just call getText()
		if (count($args) == 0) {
			$paramValue = $wgRequest->getText($wgSimpleFormsRequestPrefix.$paramName);
			return $paramValue;
		}

		# when the parameter is a scalar calling getArray() puts it in an
		# array and returns the array, so we need to do a scalar check
		if (!is_null($wgRequest->getVal($wgSimpleFormsRequestPrefix.$paramName))) return '';

		# get the array associated with this parameter name
		$paramValue = $wgRequest->getArray($wgSimpleFormsRequestPrefix.$paramName);

		# time to descend into the depths of the array associated with the
		# parameter name
		while (count($args) > 0) {
			$key = array_shift($args);

			# do we have more keys than we have array nests?
			if (!is_array($paramValue)) return '';

			# a little closer to the value we want
			$paramValue = $paramValue[$key];
		}

		# do we have more array nests than we have keys, or a null?
		if (is_array($paramValue) || is_null($paramValue)) return '';

		# we've found a param value!
		$paramValue = str_replace("\r\n", "\n", $wgContLang->recodeInput($paramValue));

		return $paramValue;
	}

	/**
	 * requestMagic() returns an empty string under three conditions:
	 *   1) no such parameter was passed via the request,
	 *   2) the specified parameter is an array, and
	 *   3) the specified parameter was set to an empty string.
	 * Because of this we need a function to determine which is the case.  This
	 * function returns '0' if the parameter doesn't exist, '1' if the parameter
	 * is a scalar, and '2' if the parameter is an array.
	 */
	function paramTypeMagic(&$parser) {
		global $wgRequest, $wgSimpleFormsRequestPrefix;

		$args = func_get_args();

		# the first arg is the parser, we already have it by
		# reference, so we can remove it from the array
		array_shift($args);

		# get the request parameter name
		$paramName = array_shift($args);

		# only thing left in $args at this point are the array keys
		# If no keys are specified, we just try to get a scalar
		if ( count($args) == 0) {
			$paramValue = $wgRequest->getVal($wgSimpleFormsRequestPrefix.$paramName);
			if (is_null($paramValue)) {
				# getVal() returns null if the reqest parameter is an array, so
				# we need to verify that the parameter was not passed.
				$paramValue = $wgRequest->getArray($wgSimpleFormsRequestPrefix.$paramName);
				return is_null($paramValue) ? '0' : '2';
			}

			# found a scalar
			return '1';
		}

		# when the parameter is a scalar calling getArray() puts it in an
		# array and returns the array, so we need to do a scalar check
		if (!is_null($wgRequest->getVal($wgSimpleFormsRequestPrefix.$paramName))) return '0';

		# get the array associated with this parameter name
		$paramValue = $wgRequest->getArray($wgSimpleFormsRequestPrefix.$paramName);

		# descend into the depths of the array
		while (count($args) > 0) {
			$key = array_shift($args);

			# do we have more keys than we have array nests?
			if (!is_array($paramValue) || !array_key_exists($key, $paramValue)) return '0';

			# a little closer to the value we want
			$paramValue = $paramValue[$key];
		}

		# do we have more array nests than we have keys?
		return is_array($paramValue) ? '2' : '1';
	}

	/**
	 * Return the raw content
	 */
	function raw($text) {
		global $wgOut,$wgParser,$wgRequest;
		$this->setCaching();
		$expand = $wgRequest->getText('templates') == 'expand';
		if ($expand) $text = $wgParser->preprocess($text,new Title(),new ParserOptions());
		$wgOut->disable();
		wfResetOutputBuffers();
		header('Content-Type: application/octet-stream');
		echo($text);
		return false;
	}

	/**
	 * Return rendered content of page
	 */
	function render(&$out, &$text) {
		$this->setCaching();
		$out->disable();
		wfResetOutputBuffers();
		echo($text);
		return false;
	}

	/**
	 * Disable caching if necessary
	 */
	function setCaching() {
		global $wgOut, $wgEnableParserCache, $wgSimpleFormsEnableCaching;
		if ($wgSimpleFormsEnableCaching) return;
		$wgOut->enableClientCache(false);
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	}

	/**
	 * Processes HTTP requests containing wikitext content
	 */
	function processRequest() {
		global $wgOut, $wgRequest, $wgUser, $wgTitle,
			   $wgSimpleFormsAllowRemoteAddr, $wgSimpleFormsAllowCreate, $wgSimpleFormsAllowEdit;

		$content = trim($wgRequest->getText(SIMPLEFORMS_CONTENT));
		$action  = $wgRequest->getText('action');
		$title   = $wgRequest->getText('title');

		# Handle content with action=raw case (allows templates=expand too)
		if ($action == 'raw' && isset($_REQUEST[SIMPLEFORMS_CONTENT])) $this->raw($content);

		# Handle content and title case (will either update or create an article)
		if ($title != SIMPLEFORMS_UNTITLED && isset($_REQUEST[SIMPLEFORMS_CONTENT])) {

			$title   = Title::newFromText($wgRequest->getText('title'));
			if ($title->getNamespace() == NS_SPECIAL) return;
			if (!is_object($wgTitle)) $wgTitle = $title; # hack to stop DPL crashing
			$article = new Article($title);
			$allow   = in_array($_SERVER['REMOTE_ADDR'], $wgSimpleFormsAllowRemoteAddr);
			$summary = $wgRequest->getText(SIMPLEFORMS_SUMMARY);
			$minor   = $wgRequest->getText(SIMPLEFORMS_MINOR);
			$return  = $wgRequest->getText(SIMPLEFORMS_RETURN);

			# If title exists and allowed to edit, prepend/append/replace content
			if ($title->exists()) {
				if ($wgSimpleFormsAllowEdit && ($allow || $wgUser->isAllowed('edit'))) {
					$update = $this->updateTemplates($article->getContent(),$content);
					$article->updateArticle($update, $summary ? $summary : wfMsg('sf_editsummary'), false, false);
				}
				else $wgOut->setPageTitle(wfMsg('whitelistedittitle'));
			}

			# No such title, create new article from content if allowed to create
			else {
				if ($wgSimpleFormsAllowCreate && ($allow || $wgUser->isAllowed('edit')))
					$article->insertNewArticle($content, $summary ? $summary : wfMsg('sf_editsummary', 'created'), false, false);
				else $wgOut->setPageTitle(wfMsg('whitelistedittitle'));
			}

			# If returnto is set, add a redirect header and die
			if ($return) die(header('Location: '.Title::newFromText($return)->getFullURL()));
		}
	}

	/**
	 * Create a dummy article for rendering content not associated with any title (unless it already exists)
	 * - there's probably a better way to do this
	 */
	function createUntitled() {
		$title = Title::newFromText(SIMPLEFORMS_UNTITLED);
		if (!$title->exists()) {
			$article = new Article($title);
			$article->insertNewArticle(
				'Dummy article used by [http://www.mediawiki.org/wiki/Extension:Simple_Forms Extension:SimpleForms]',
				'Dummy article created for Simple Forms extension',
				true,
				false
			);
		}
	}

	/**
	 * Update templates wikitext content
	 * - $updates must start and end with double-braces
	 * - $updates may contain multiple template updates
	 * - each update must only match one template, comparison of args will reduce multiple matches
	 */
	function updateTemplates($content, $updates) {
		global $wgRequest;
		$caction = $wgRequest->getText(SIMPLEFORMS_CACTION);
		$taction = $wgRequest->getText(SIMPLEFORMS_TACTION);
		$regexp  = $wgRequest->getText(SIMPLEFORMS_REGEXP);

		# Resort to normal content-action if $updates is not exclusively template definitions or updating templates disabled
		if ($taction == 'update' and preg_match('/^\\{\\{.+\\}\\}$/is', $updates, $match)) {

			# pattern to extract the first name and value of the first arg from template definition
			$pattern = '/^.+?[:\\|]\\s*(\\w+)\\s*=\\s*(.*?)\\s*[\\|\\}]/s';
			$addtext = '';

			# Get the offsets and lengths of template definitions in content and updates wikitexts
			$cbraces = $this->examineBraces($content);
			$ubraces = $this->examineBraces($updates);

			# Loop through the top-level braces in $updates
			foreach ($ubraces as $ubrace) if ($ubrace[SFEB_DEPTH] == 1) {

				# Get the update text
				$utext = substr($updates,$ubrace[SFEB_OFFSET],$ubrace[SFEB_LENGTH]);

				# Get braces in content with the same name as this update
				$matches = array();
				$uname   = $ubrace[SFEB_NAME];
				foreach ($cbraces as $ci => $cbrace) if ($cbrace[SFEB_NAME] == $uname) $matches[] = $ci;

				# If more than one matches, try to reduce to one by comparing the first arg of each with the updates first arg
				if (count($matches) > 1 && preg_match($pattern, $utext, $uarg)) {
					$tmp = array();
					foreach ($matches as $ci) {
						$cbrace = &$cbraces[$ci];
						$cbtext = substr($content, $cbrace[SFEB_OFFSET], $cbrace[SFEB_LENGTH]);
						if (preg_match($pattern, $cbtext, $carg) && $carg[1] == $uarg[1] && $carg[2] == $uarg[2])
							$tmp[] = $ci;
					}
					$matches = &$tmp;
				}

				# If matches has been reduced to a single item, update the template in the content
				if (count($matches) == 1) {
					$coffset = $cbraces[$matches[0]][SFEB_OFFSET];
					$clength = $cbraces[$matches[0]][SFEB_LENGTH];
					$content = substr_replace($content, $utext, $coffset, $clength);
				}

				# Otherwise (if no matches, or many matches) do normal content-action on the update
				else $addtext .= "$utext\n";
			}
		}

		# Do normal content-action if $updates was not purely templates
		else $addtext = $updates;

		# Do regular expression replacement if regexp parameter set
		$addtext = trim($addtext);
		$content = trim($content);
		if ($regexp) {
			$content = preg_replace("|$regexp|", $addtext, $content, -1, $count);
			if ($count) $addtext = false;
		}

		# Add any prepend/append updates using the content-action
		if ($addtext) {
			if	 ($caction == 'prepend') $content = "$addtext\n$content";
			elseif ($caction == 'append')  $content = "$content\n$addtext";
			elseif ($caction == 'replace') $content = $addtext;
		}

		return $content;
	}

	/**
	 * Return a list of info about each template definition in the passed wikitext content
	 * - list item format is NAME, OFFSET, LENGTH, DEPTH
	 */
	function examineBraces(&$content) {
		$braces = array();
		$depths = array();
		$depth = 1;
		$index = 0;
		while (preg_match('/\\{\\{\\s*([#a-z0-9_]*)|\\}\\}/is', $content, $match, PREG_OFFSET_CAPTURE, $index)) {
			$index = $match[0][1]+2;
			if ($match[0][0] == '}}') {
				$brace = &$braces[$depths[$depth-1]];
				$brace[SFEB_LENGTH] = $match[0][1]-$brace[SFEB_OFFSET]+2;
				$brace[SFEB_DEPTH] = --$depth;
			}
			else {
				$depths[$depth++] = count($braces);
				$braces[] = array(SFEB_NAME => $match[1][0], SFEB_OFFSET => $match[0][1]);
			}
		}
		return $braces;
	}

	/**
	 * Needed in some versions to prevent Special:Version from breaking
	 */
	function __toString() { return 'SimpleForms'; }

}

/**
 * Called from $wgExtensionFunctions array when initialising extensions
 */
function wfSetupSimpleForms() {
	global $wgLanguageCode,$wgMessageCache,$wgHooks,$wgRequest,$wgSimpleForms;

	# Add messages
	if ($wgLanguageCode == 'en') {
		$wgMessageCache->addMessages(array(
			'sf_editsummary' => 'Article updated via HTTP request'
		));
	}

	# Instantiate a singleton for the extension
	$wgSimpleForms = new SimpleForms();
}

/**
 * Needed in MediaWiki >1.8.0 for magic word hooks to work properly
 */
function wfSimpleFormsLanguageGetMagic(&$magicWords,$langCode = 0) {
	global $wgSimpleFormsFormMagic, $wgSimpleFormsInputMagic, $wgSimpleFormsRequestMagic, $wgSimpleFormsParaTypeMagic;
	$magicWords[$wgSimpleFormsFormMagic]     = array(0, $wgSimpleFormsFormMagic);
	$magicWords[$wgSimpleFormsInputMagic]    = array(0, $wgSimpleFormsInputMagic);
	$magicWords[$wgSimpleFormsRequestMagic]  = array(0, $wgSimpleFormsRequestMagic);
	$magicWords[$wgSimpleFormsParaTypeMagic] = array(0, $wgSimpleFormsParaTypeMagic);
	return true;
}