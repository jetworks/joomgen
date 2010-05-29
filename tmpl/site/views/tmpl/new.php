<?php
/**
 * {{viewClass}} HTML Default Template
 *
 * PHP versions 5
 *
 * @category  Template
 * @package   {{identifier}}
 * @author    {{author_name}} <{{author_email}}>
 * @copyright {{copyright}}
 * @license   {{license}}
 * @link      {{author_url}}
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.keepalive');
{{cal}}
{{rte}}
?>

<h1><?php echo JText::_('Add {{entity}}'); ?></h1>
<form id="new_{{view}}" name="new_{{view}}" method="post" onsubmit="return document.formvalidator.isValid(this)">
<table border="0" cellspacing="1" cellpadding="1">
{{widgets}}
</table>
    <?php echo JHTML::_('form.token'); ?>
    <input type="submit" value="<?php echo JText::_('Submit'); ?>" />
    <input type="hidden" name="option" value="{{component}}" /> 
    <input type="hidden" name="task" value="save" /> 
    <input type="hidden" name="view" value="{{view}}" /> 
    <input type="hidden" name="controller" value="{{view}}" />
</form>