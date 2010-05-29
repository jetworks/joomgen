<?php
/**
 * {{viewClass}} HTML List Template
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
?>

<h1><?php echo JText::_('{{entity}}'); ?></h1>
<?php if(count($this->items) > 0) : ?>
<div id="{{view}}_list">
    <p><?php echo JText::_('Below is a list of all current {{entity}}'); ?>.</p>
    <table border="0" cellspacing="1" cellpadding="1">
    <thead>
        <tr>
{{fields_headers}}
        </tr>
    </thead>

    <?php foreach ($this->items as $item) : ?>
    <?php $link = JRoute::_('index.php?option={{component}}&amp;view={{controller}}&amp;layout=details&amp;id='.$item->id); ?>
        <tr>
{{fields_values}}
        </tr>
    <?php endforeach; ?>
    </table>
<?php else: ?>
    <p><?php echo JText::_('No {{entity}} found'); ?>.</p>
<?php endif; ?>
</div>