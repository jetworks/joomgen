<?php
/**
 * {{viewClass}} HTML View Class
 *
 * PHP versions 5
 *
 * @category  View
 * @package   {{identifier}}
 * @author    {{author_name}} <{{author_email}}>
 * @copyright {{copyright}}
 * @license   {{license}}
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the {{identifier}} component
 *
 * @category View
 * @package  {{identifier}}
 * @author   {{author_name}} <{{author_email}}>
 * @license  {{license}}
 * @link     {{author_url}}
 * @since    1.0
 */
class {{identifier}}View{{viewClass}} extends JView
{
    /**
     * Display the view
     *
     * @param string $tpl Template
     *
     * @return void
     * @access public
     * @since  1.0
     */
    function display($tpl = null)
    {
        // Handle different data for different layouts
        $layout = JRequest::getVar('layout');
        if($layout == "list") {
            $this->assignRef('items', $this->get('Items'));
        } else {
            $this->assignRef('item', $this->get('Item'));
        }

        parent::display($tpl);
    }
}
