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
{{cal}}
{{rte}}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="col100">
        <fieldset class="adminform"><legend><?php echo JText::_(''); ?></legend>
        <table class="admintable">
{{widgets}}
            <tr></tr>
        </table>
        </fieldset>
    </div>

    <div class="clr"></div>

    <input type="hidden" name="option" value="{{component}}" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="view" value="{{view}}" />
    <input type="hidden" name="controller" value="{{view}}" />
    <input type="hidden" name="task" value="save" />
</form>
