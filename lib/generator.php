<?php
/**
 * Main JoomGen file: a collection of methods used for
 * generating Joomla! code
 *
 * PHP versions 5
 *
 * @package   JoomGen
 * @author    Dirceu Pereira Tiegs <dirceu@jetworks.com.br>
 * @copyright 2010 JetWorks. All rights reserved.
 * @license   GNU General Public License
 * @version   2.0
 * @link      http://jetworks.com.br
 */

// it's better to have a shorthand, since we use it so much
define('DS', DIRECTORY_SEPARATOR);

// import the YAML parsing library
include_once('lib'.DS.'spyc.php');

/**
 * Transforms a variable name into a human-readable format.
 * Ex.: title('how_to_apply') == 'How To Apply'
 *
 * @return string
 * @access public
 * @since  2.0
 */
function title($key) {
    return ucwords(str_replace('_', ' ', $key));
}

/**
 * Delete a non-empty directory.
 * from: http://www.webcheatsheet.com/PHP/working_with_directories.php
 *
 * @return void
 * @access public
 * @since  2.0
 */
function delete_dir($dir) {
    $dhandle = opendir($dir);

    if ($dhandle) {
        while (false !== ($fname = readdir($dhandle))) {
            if (is_dir("{$dir}".DS."{$fname}")) {
                if (($fname != '.') && ($fname != '..')) {
                    delete_dir($dir.DS.$fname);
                }
            } else {
                unlink($dir.DS.$fname);
            }
        }
        closedir($dhandle);
    }
    rmdir($dir);
}

/**
 * Get configuration from the component.yaml file as an array.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_config()
{
    $yaml_config = Spyc::YAMLLoad('config'.DS.'component.yaml');
    $config = array();
    foreach ($yaml_config as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $config[$key.'_'.$k] = $v;
            }
        } else {
            $config[$key] = $value;
        }
    }

    // special variables
    $config['entry_point'] = strtolower($config['identifier']).'.php';
    $config['submenu'] = '';

    // raise error if it doesn't find the expected keys
    $expected_keys = array('name', 'identifier', 'component', 'database_engine',
                           'database_default_charset', 'default_language',
                           'entry_point', 'submenu');
    if($not_found = array_diff_key(array_flip($expected_keys), $config)) {
        throw new Exception('You need to provide the following keys on component.yaml: '.implode(', ', array_keys($not_found)));
    }

    return $config;
}

/**
 * Get informations about the models from models.yaml as an array.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_models()
{
    $yaml_models = Spyc::YAMLLoad('config'.DS.'models.yaml');
    $models = array();
    foreach ($yaml_models as $model => $attrs) {
        $models[$model] = array();
        foreach ($attrs as $key => $value) {
            if (!is_array($value)) {
                $value = array('type' => $value);
            }

            // set defaults
            $defaults = array('required' => true, 'description' => title($key));
            $value = array_merge($defaults, $value);

            $models[$model][$key] = $value;
        }
    }

    return $models;
}

/**
 * Get configuration from the frontend.yaml file as an array.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_frontend()
{
    $yaml_frontend = Spyc::YAMLLoad('config'.DS.'frontend.yaml');
    $frontend = array();
    foreach ($yaml_frontend as $model => $views) {
        $frontend[$model] = array();
        foreach ($views as $view => $fields) {
            $frontend[$model][$view] = explode(' ', $fields);
        }
    }
    return $frontend;
}

/**
 * Teeny tiny template renderer. Parse a file and replace all occurrences
 * of {{key}} with $data[$key].
 *
 * @param string $template Template to be proccessed
 * @param array  $data     Data needed by the template
 *
 * @return string
 * @access public
 * @since  2.0
 */
function render($template, $data)
{
    $re = "\\{\\{(\w+)\\}\\}";
    preg_match_all("/$re/is", $template, $matches);
    foreach($matches[0] as $match) {
        $key = str_replace("}}", "", str_replace("{{", "", $match));
        $value = $data[$key];
        $template = str_replace($match, $value, $template);
    }
    return $template;
}

/**
 * Generates directory structure for the whole component, a manifest.xml file,
 * controllers and entry points for the frontend and the backend.
 *
 * @return void
 * @access public
 * @since  2.0
 */
function generate_output($config, $models, $frontend)
{
    $component = $config['component'];
    $base_path = $component.DS;
    $lang_path = 'language'.DS.$config['default_language'];
    $lang_file = DS.$config['default_language'].'.'.$config['component'].'.ini';

    // create fresh output directory
    @delete_dir($component);
    mkdir($component);

    // generate directory structure for the backend
    mkdir($base_path.'admin');
    $admin_path = $base_path.'admin'.DS;
    foreach (array('assets', 'controllers', 'help', 'helpers', 'install', 'language', 'models', 'tables', 'views', $lang_path) as $dir) {
        mkdir($admin_path.$dir);
        file_put_contents($admin_path.$dir.DS.'index.html', 'test');
    }

    // generate config.xml file
    file_put_contents($admin_path.DS.'config.xml', file_get_contents('tmpl'.DS.'admin'.DS.'config.xml'));

    // generate SQL install / uninstall files
    if($models) {
        $sql = prepare_sql($config, $models);
    } else {
        $sql = array('', '');
    }
    file_put_contents($admin_path.'install'.DS.'installsql.mysql.utf8.php', $sql[0]);
    file_put_contents($admin_path.'install'.DS.'uninstallsql.mysql.utf8.php', $sql[1]);

    // generate tables, models, controllers and views for the backend
    if($models) {
        foreach (prepare_tables($config, $models) as $name => $content) {
            file_put_contents($admin_path.'tables'.DS.$name, $content);
        }
        foreach (prepare_models_or_controllers($config, $models, array(), 'model') as $name => $content) {
            file_put_contents($admin_path.'models'.DS.$name, $content);
        }
        foreach (prepare_models_or_controllers($config, $models, array(), 'controller') as $name => $content) {
            file_put_contents($admin_path.'controllers'.DS.$name, $content);
        }
        $default_view_files = prepare_default_view($config, array());
        $view_path = $admin_path.'views'.DS.strtolower($config['identifier']);
        mkdir($view_path);
        mkdir($view_path.DS.'tmpl');
        foreach ($default_view_files as $name => $content) {
          file_put_contents($view_path.DS.$name, $content);
        }
        foreach (prepare_views_for_backend($config, $models) as $name => $views) {
            $view_path = $admin_path.'views'.DS.$name;
            mkdir($view_path);
            mkdir($view_path.DS.'tmpl');
            foreach ($views as $name => $content) {
                file_put_contents($view_path.DS.$name, $content);
            }
        }
        $submenu = "    <submenu>\n";
        foreach ($models as $model => $attrs) {
            if($attrs['sql_only']) continue;
            $submenu .= "      <menu link='option=$component&amp;view=$model&amp;layout=list'>$model</menu>\n";
        }
        $submenu .= "    </submenu>\n";
        $config['submenu'] = $submenu;
    }

    // generate empty language file for the backend
    file_put_contents($admin_path.$lang_path.$lang_file, '');

    // generate directory structure for the frontend
    mkdir($base_path.'site');
    $site_path = $base_path.'site'.DS;
    foreach (array('assets', 'controllers', 'helpers', 'language', 'models', 'views', $lang_path) as $dir) {
        mkdir($site_path.$dir);
        file_put_contents($site_path.$dir.DS.'index.html', 'test');
    }

    // generate models, controllers and views for the frontend
    if($models) {
        foreach (prepare_models_or_controllers($config, $models, $frontend, 'model', 'site') as $name => $content) {
            file_put_contents($site_path.'models'.DS.$name, $content);
        }
        foreach (prepare_models_or_controllers($config, $models, $frontend, 'controller', 'site') as $name => $content) {
            file_put_contents($site_path.'controllers'.DS.$name, $content);
        }
        $default_view_files = prepare_default_view($config, $frontend, 'site');
        $view_path = $site_path.'views'.DS.strtolower($config['identifier']);
        mkdir($view_path);
        mkdir($view_path.DS.'tmpl');
        foreach ($default_view_files as $name => $content) {
          file_put_contents($view_path.DS.$name, $content);
        }
        foreach (prepare_views_for_frontend($config, $models, $frontend) as $name => $views) {
            $view_path = $site_path.'views'.DS.$name;
            mkdir($view_path);
            mkdir($view_path.DS.'tmpl');
            foreach ($views as $name => $content) {
                file_put_contents($view_path.DS.$name, $content);
            }
        }
    }

    // generate empty language file for the frontend
    file_put_contents($site_path.$lang_path.$lang_file, '');

    // generate manifest file
    $template = file_get_contents('tmpl'.DS.'manifest.xml');
    file_put_contents($base_path.'manifest.xml', render($template, $config));

    // generate controllers
    $template = file_get_contents('tmpl'.DS.'controller.php');
    $rendered_template = render($template, $config);
    foreach (array($admin_path, $site_path) as $path) {
        file_put_contents($path.'controller.php', $rendered_template);
    }

    // generate entry points
    $template = file_get_contents('tmpl'.DS.'entry_point.php');

    $config['include_tables'] = "JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');\n\n";
    file_put_contents($admin_path.$config['entry_point'], render($template, $config));

    $config['include_tables'] = '';
    file_put_contents($site_path.$config['entry_point'], render($template, $config));

    // create .tar.gz installer if it's on a unix-like OS
    if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        @unlink("$component.tar.gz");
        system("tar -zcf $component.tar.gz $component");
        delete_dir($component);
    }
}

/**
 * Generates the SQL install / uninstall files.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_sql($config, $models)
{
    $types = array('string'    => 'VARCHAR(255)',
                   'text'      => 'TEXT',
                   'int'       => 'INT(11)',
                   'datetime'  => 'DATETIME',
                   'date'      => 'DATE',
                   'time'      => 'TIME',
                   'double'    => 'DOUBLE',
                   'decimal'   => 'DECIMAL',
                   'rich_text' => 'TEXT',
                   'bool'      => 'INT(1)');
    $required = array(true  => 'NOT NULL',
                      false => 'DEFAULT NULL');
    $table_prefix = '#__'.str_replace('com_', '', $config['component']).'_';
    $install_file = '';
    $uninstall_file = '';

    foreach ($models as $model => $attrs) {
        $uninstall_file .= "DROP TABLE IF EXISTS `$table_prefix$model`;\n";
        $install_file .= "CREATE TABLE IF NOT EXISTS `$table_prefix$model` (\n";
        $install_file .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
        foreach ($attrs as $key => $value) {
            if($key == 'sql_only') continue;
            $install_file .= "  `$key` ".$types[$value['type']]." ".$required[$value['required']].",\n";
        }
        $install_file .= "  PRIMARY KEY  (`id`)\n";
        $install_file .= ") ENGINE=".$config['database_engine'].
                         " DEFAULT CHARSET=".$config['database_default_charset'].";\n\n";
    }

    return array($install_file, $uninstall_file);
}

/**
 * Generates JTable subclasses.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_tables($config, $models)
{
    $defaults = array('string'    => "''",
                      'text'      => "''",
                      'int'       => 0,
                      'datetime'  => 'null',
                      'date'      => 'null',
                      'time'      => 'null',
                      'double'    => 0,
                      'decimal'   => 0,
                      'rich_text' => "''",
                      'bool'      => 0);
    $table_prefix = '#__'.str_replace('com_', '', $config['component']).'_';
    $template = file_get_contents('tmpl'.DS.'admin'.DS.'table.php');
    $files = array();

    foreach ($models as $model => $attrs) {
        if($attrs['sql_only']) continue;
        $config['model'] = ucfirst($model);
        $config['table_name'] = $table_prefix.$model;
        $properties = '';
        $required_fields = array();
        foreach ($attrs as $key => $value) {
            if ($value['required'] && $key != 'published') {
                $required_fields[] = "'$key' => '".$value['description']."'";
            }
            $properties .= "\n    /**\n     * @var ".$value['type']." ".$value['description']."\n     */\n    var \$$key = ".$defaults[$value['type']].";\n";
        }
        $config['required_fields'] = implode(', ', $required_fields);
        $config['properties'] = $properties;

        $files["$model.php"] = render($template, $config);
    }

    return $files;
}

/**
 * Generates JModel and JController subclasses.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_models_or_controllers($config, $models, $views, $type='model', $for='admin')
{
    $table_prefix = '#__'.str_replace('com_', '', $config['component']).'_';
    $template = file_get_contents('tmpl'.DS.$for.DS.$type.'.php');
    $files = array();

    foreach ($models as $model => $attrs) {
        if($attrs['sql_only']) continue;
        $config[$type] = ucfirst($model);
        $config['table_name'] = $table_prefix.$model;
        $config['rich_text_fields'] = '';
        foreach ($attrs as $key => $value) {
            if($value['type'] == 'rich_text') $config['rich_text_fields'] .= "		\$data['$key'] = JRequest::getVar('$key', '', 'post', 'string', JREQUEST_ALLOWRAW);\n";
            if($key == 'published' && $for='admin') {
                $config['register_publish'] = "\$this->registerTask('unpublish', 'publish');";
                $tmpl_publish = file_get_contents('tmpl'.DS.'admin'.DS.'snippets'.DS.'publish.php');
                $config['publish_function'] = render($tmpl_publish, $config);
            }
        }
        if(in_array('published', array_keys($attrs))) {
            $config['single_publish'] = ' AND `published` = 1 ';
            $config['multi_publish'] = ' WHERE `published` = 1 ';
        } else {
            $config['single_publish'] = $config['multi_publish'] = '';
        }
        $files["$model.php"] = render($template, $config);
    }

    return $files;
}

/**
 * Generates JView subclasses and templates for the backend.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_views_for_backend($config, $models)
{
    $views_path = 'tmpl'.DS.'admin'.DS.'views'.DS;
    $class_template = file_get_contents($views_path.'view.html.php');
    $list_template = file_get_contents($views_path.'tmpl'.DS.'list.php');
    $default_template = file_get_contents($views_path.'tmpl'.DS.'default.php');
    $files = array();

    // mapping of widgets / input types
    $widgets_path = 'tmpl'.DS.'widgets'.DS;
    $widget_types = array('string' => file_get_contents($widgets_path.'string.html'),
                          'text' => file_get_contents($widgets_path.'text.html'),
                          'rich_text' => file_get_contents($widgets_path.'rich_text.html'),
                          'int' => file_get_contents($widgets_path.'string.html'),
                          'double' => file_get_contents($widgets_path.'string.html'),
                          'decimal' => file_get_contents($widgets_path.'string.html'),
                          'date' => file_get_contents($widgets_path.'datetime.html'),
                          'time' => file_get_contents($widgets_path.'datetime.html'),
                          'datetime' => file_get_contents($widgets_path.'datetime.html'),
                          'bool' => file_get_contents($widgets_path.'bool.html'));

    // different date formats for the date/time fields
    $date_formats = array('datetime' => '%Y-%m-%d %H:%M:00',
                          'date' => '%Y-%m-%d',
                          'time' => '%H:%M:00');

    // rich text editor boilerplate code
    $rte  = "\$editor =& JFactory::getEditor();\n";
    $rte .= "\$params = array('smilies'=> '0', 'html' => '1', 'style'  => '1', 'layer'  => '0', 'table'  => '1', 'clear_entities'=>'0');";

    // behavior required for showing the calendar
    $cal = "JHTML::_('behavior.calendar');\n";

    foreach ($models as $model => $attrs) {
        // by default, we don't need it
        $config['rte'] = $config['cal'] = '';
        if($attrs['sql_only']) continue;
        $config['viewClass'] = ucfirst($model);
        $config['view'] = $model;

        $config['fields_headers'] = $config['fields_values'] = $config['widgets'] = '';
        foreach ($attrs as $key => $value) {
            $config['fields_headers'] .= "                  <th><?php echo JText::_('".$value['description']."'); ?></th>\n";
            if($key == 'published') {
                $config['fields_values'] .= "                       <td><?php echo JHTML::_('grid.published', \$row, \$i); ?></td>\n";
            } else {
                $config['fields_values'] .= "                       <td><?php echo \"<a href='\$link'>\".\$row->$key.\"</a>\"; ?></td>\n";
            }
            $widget_config = array('key'=>$key, 'description'=>$value['description']);
            if(in_array($value['type'], array('date', 'time', 'datetime'))) {
                $widget_config['date_format'] = $date_formats[$value['type']];
                $config['cal'] = $cal;
            }
            $config['widgets'] .= render($widget_types[$value['type']], $widget_config);

            if($value['type'] == 'rich_text') {
                $config['rte'] = $rte;
            }
        }

        $files[$model] = array('view.html.php' => render($class_template, $config),
                               'tmpl'.DS.'list.php' => render($list_template, $config),
                               'tmpl'.DS.'default.php' => render($default_template, $config));
    }

    return $files;
}

/**
 * Generates JView subclasses and templates for the frontend.
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_views_for_frontend($config, $models, $views)
{
    $views_path = 'tmpl'.DS.'site'.DS.'views'.DS;
    $class_template = file_get_contents($views_path.'view.html.php');
    $details_template = file_get_contents($views_path.'tmpl'.DS.'details.php');
    $details_params = file_get_contents($views_path.'tmpl'.DS.'details.xml');
    $list_template = file_get_contents($views_path.'tmpl'.DS.'list.php');
    $list_params = file_get_contents($views_path.'tmpl'.DS.'list.xml');
    $new_template = file_get_contents($views_path.'tmpl'.DS.'new.php');
    $new_params = file_get_contents($views_path.'tmpl'.DS.'new.xml');
    $table_prefix = '#__'.str_replace('com_', '', $config['component']).'_';

    $files = array();

    // mapping of widgets / input types
    $widgets_path = 'tmpl'.DS.'widgets'.DS;
    $widget_types = array('string' => file_get_contents($widgets_path.'string.html'),
                          'text' => file_get_contents($widgets_path.'text.html'),
                          'rich_text' => file_get_contents($widgets_path.'rich_text.html'),
                          'int' => file_get_contents($widgets_path.'string.html'),
                          'double' => file_get_contents($widgets_path.'string.html'),
                          'decimal' => file_get_contents($widgets_path.'string.html'),
                          'date' => file_get_contents($widgets_path.'datetime.html'),
                          'time' => file_get_contents($widgets_path.'datetime.html'),
                          'datetime' => file_get_contents($widgets_path.'datetime.html'),
                          'bool' => file_get_contents($widgets_path.'bool.html'));

    // different date formats for the date/time fields
    $date_formats = array('datetime' => '%Y-%m-%d %H:%M:00',
                        'date' => '%Y-%m-%d',
                        'time' => '%H:%M:00');

    // rich text editor boilerplate code
    $rte  = "\$editor =& JFactory::getEditor();\n";
    $rte .= "\$params = array('smilies'=> '0', 'html' => '1', 'style'  => '1', 'layer'  => '0', 'table'  => '1', 'clear_entities'=>'0');";

    // behavior required for showing the calendar
    $cal = "JHTML::_('behavior.calendar');\n";

    foreach ($models as $model => $attrs) {
        if($attrs['sql_only'] || !in_array($model, array_keys($views))) continue;

        $has_details = in_array('details', array_keys($views[$model]));
        $has_new = in_array('new', array_keys($views[$model]));
        $has_list = in_array('list', array_keys($views[$model]));

        $config['rte'] = $config['cal'] = '';
        $config['viewClass'] = ucfirst($model);
        $config['model'] = $config['view'] = $config['controller'] = $model;
        $config['entity'] = title($model);
        $table_name = $table_prefix.$model;

        $config['fields_headers'] = $config['fields_values'] = $config['widgets'] = $config['details'] = '';
        foreach ($attrs as $key => $value) {
            if($has_list && in_array($key, $views[$model]['list'])) {
                $config['fields_headers'] .= "            <th><?php echo JText::_('".$value['description']."'); ?></th>\n";
                $config['fields_values'] .= "            <td><?php echo \"<a href='\$link'>\".\$item->$key.\"</a>\"; ?></td>\n";
            }
            if($has_new && in_array($key, $views[$model]['new'])) {
                $widget_config = array('key'=>$key, 'description'=>$value['description']);
                if(in_array($value['type'], array('date', 'time', 'datetime'))) {
                    $widget_config['date_format'] = $date_formats[$value['type']];
                    $config['cal'] = $cal;
                }
                $config['widgets'] .= render($widget_types[$value['type']], $widget_config);
                if($value['type'] == 'rich_text') {
                    $config['rte'] = $rte;
                }
            }
            if($has_details && in_array($key, $views[$model]['details'])) {
                $config['details'] .= "<tr>\n    <th><?php echo JText::_('".$value['description']."'); ?></th>\n";
                $config['details'] .= "    <td><?php echo \$this->item->$key; ?></td></tr>\n";
                $config['query'] = "SELECT `id`, `id` AS `title` FROM `$table_name`";
            }
        }

        $files[$model] = array('view.html.php' => render($class_template, $config));
        if($has_list) {
            $files[$model]['tmpl'.DS.'list.php'] = render($list_template, $config);
            $files[$model]['tmpl'.DS.'list.xml'] = render($list_params, $config);
        }
        if($has_details) {
            $files[$model]['tmpl'.DS.'details.php'] = render($details_template, $config);
            $files[$model]['tmpl'.DS.'details.xml'] = render($details_params, $config);
        }
        if($has_new) {
            $files[$model]['tmpl'.DS.'new.php'] = render($new_template, $config);
            $files[$model]['tmpl'.DS.'new.xml'] = render($new_params, $config);
        }
    }

    return $files;
}

/**
 * Generates the default view for a component
 *
 * @return array
 * @access public
 * @since  2.0
 */
function prepare_default_view($config, $views, $for='admin')
{
    $view_path = 'tmpl'.DS.$for.DS.'default_view'.DS;
    $class_template = file_get_contents($view_path.'view.html.php');
    $default_template = file_get_contents($view_path.'tmpl'.DS.'default.php');

    if($for == 'site') {
        $option = $config['component'];
        $links_to_views = '<ul>';
        foreach ($views as $view => $layouts) {
            foreach ($layouts as $layout => $fields) {
                $link = "<?php echo JRoute::_('index.php?option=$option&view=$view&layout=$layout'); ?>";
                $links_to_views .= '<li><a href="'.$link.'">'.title($view).' '.title($layout).'</a></li>';
            }
        }
        $links_to_views .= '</ul>';
        $config['links_to_views'] = $links_to_views;
    }

    return array('view.html.php' => render($class_template, $config),
                 'tmpl'.DS.'default.php' => render($default_template, $config));
}