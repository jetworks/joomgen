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
jimport('joomla.filter.filteroutput');
?>

<table class="adminform"><tr><td>
<div id="{{view}}">
	<form action="index.php" method="post" name="adminForm">
   	<div id="editcell">
       	<table class="adminlist">
       	<thead>
       		<tr>
       			<th width="5"><?php echo JText::_('ID'); ?></th>
       			<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" /></th>
{{fields_headers}}
       		</tr>
       	</thead>
       	<?php
       	    $k = 0;
       	    $i = 0;
       	    foreach($this->items as $row) 
       	    {
       	        JFilterOutput::objectHTMLSafe($row);
           		$checked = JHTML::_('grid.id', $i, $row->id);
           		$link = JRoute::_('index.php?option={{component}}&view={{view}}&task=edit&cid[]='. $row->id);
       	?>
           	    <tr class="<?php echo "row$k"; ?>">
               		<td><?php echo $row->id; ?></td>
               		<td><?php echo $checked; ?></td>
{{fields_values}}
               	</tr>
       	<?php
           		$k = 1 - $k;
           		$i = $i + 1;
           	}
       	?>
        </table>
    </div>
    <input type="hidden" name="option" value="{{component}}" /> 
    <input type="hidden" name="task" value="" /> 
    <input type="hidden" name="boxchecked" value="0" /> 
    <input type="hidden" name="view" value="{{view}}" />
    <input type="hidden" name="controller" value="{{view}}" />
</div>
</td></tr></table>
