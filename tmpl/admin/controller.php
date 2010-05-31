<?php
/**
 * {{controller}} Controller of the {{identifier}} Component
 *
 * PHP versions 5
 *
 * @category  Controller
 * @package   {{identifier}}
 * @author    {{author_name}} <{{author_email}}>
 * @copyright {{copyright}}
 * @license   {{license}}
 * @version   {{version}}
 * @link      {{author_url}}
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * {{identifier}} Component {{controller}} Controller
 *
 * @category Controller
 * @package  {{identifier}}
 * @author   {{author_name}} <{{author_email}}>
 * @license  {{license}}
 * @link     {{author_url}}
 * @since    1.0
 */
class {{identifier}}Controller{{controller}} extends {{identifier}}Controller
{
    /**
     * Constructor
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerTask('add', 'edit');
        {{register_publish}}
    }

    /**
     * Method to edit an object
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function edit()
    {
        JRequest::setVar('view', '{{controller}}');
        JRequest::setVar('hidemainmenu', 1);
        parent::display();
    }

    /**
     * Method to save an object
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function save()
    {
        $model = $this->getModel('{{controller}}');
        if ($model->save()) {
            $msg = JText::_('Object saved successfully!');
        } else {
            $msg = JText::_('Error: '.$model->getError());
        }
        $this->setRedirect(JRoute::_('index.php?option={{component}}&amp;view={{controller}}&amp;layout=list'), $msg);
    }

    /**
     * Method to remove an object
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function remove()
    {
        $model = $this->getModel('{{controller}}');
        if(!$model->delete()) {
            $msg = JText::_('Error: couldn\'t delete one or more objects');
        } else {
            $msg = JText::_('Successfully deleted objects!');
        }
        $this->setRedirect(JRoute::_('index.php?option={{component}}&amp;view={{controller}}&amp;layout=list'), $msg);
    }

    /**
     * Method to cancel an operation
     *
     * @return void
     * @access public
     * @since  1.0
     */
    public function cancel()
    {
        $msg = JText::_('Operation Aborted');
        $this->setRedirect(JRoute::_('index.php?option={{component}}&amp;view={{controller}}&amp;layout=list'), $msg);
    }

{{publish_function}}
}
