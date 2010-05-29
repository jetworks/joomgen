<?php
/**
 * Entry Point file for the {{identifier}} Component
 *
 * PHP versions 5
 *
 * @category  Entry_Point
 * @package   {{identifier}}
 * @author    {{author_name}} <{{author_email}}>
 * @copyright {{copyright}}
 * @license   {{license}}
 * @version   {{version}}
 * @link      {{author_url}}
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

{{include_tables}}/**
 * Require the base controller
 */
require_once JPATH_COMPONENT.DS.'controller.php';

// Require specific controller if requested
jimport('joomla.filesystem.file');
if($controller = JFile::makeSafe(JRequest::getWord('controller'))) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$controller_name = '{{identifier}}Controller'.ucfirst($controller);
$controller	= new $controller_name();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();