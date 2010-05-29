<?php
/**
 * {{viewClass}} HTML Details Template
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

<?php if($this->item->id > 0) : ?>
<table border="0" cellspacing="1" cellpadding="1">
{{details}}
</table>
<?php else : ?>
    <p><?php echo JText::_('{{entity}} not found'); ?>.</p>
<?php endif; ?>