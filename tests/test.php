<?php
require_once 'PHPUnit'.DIRECTORY_SEPARATOR.'Framework.php';
require_once 'lib'.DIRECTORY_SEPARATOR.'generator.php';
 
class GeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testTitle()
    {
        $simple_name = 'phone';
        $this->assertEquals('Phone', title($simple_name));
        
        $complex_name = 'contact_phone';
        $this->assertEquals('Contact Phone', title($complex_name));
    }
    
    public function testPrepareConfig()
    {
        $expected_keys = array('name', 'identifier', 'component', 'database_engine', 
                               'database_default_charset', 'default_language',
                               'entry_point', 'submenu');
        $actual_keys = array_keys(prepare_config());

        $this->assertTrue(count(array_diff($expected_keys, $actual_keys)) == 0);
    }
    
    public function testPrepareConfigShouldRaiseError()
    {
        $this->markTestIncomplete();
    }
    
    public function testPrepareModels()
    {
        $expected_keys = array('description', 'required', 'type');
        $models = prepare_models();
        
        foreach ($models as $model => $attrs) {
            foreach ($attrs as $key => $value) {
                $actual_keys = array_keys($value); sort($actual_keys);
                $this->assertEquals($expected_keys, $actual_keys);
            }
        }
    }
    
    public function testRender()
    {
        $simple_template = 'This is a {{test}}.';
        $data = array('test' => 'function');
        $this->assertEquals('This is a function.', render($simple_template, $data));
        
        $complex_template = 'This is not a {{test}}, but if it were a {{test}} it would be pretty {{cool}}.';
        $data = array('test' => 'party', 'cool' => 'awesome');
        $this->assertEquals('This is not a party, but if it were a party it would be pretty awesome.', 
                            render($complex_template, $data));
    }
    
    public function testShouldCreateAllAdminFolders()
    {
        $this->markTestIncomplete();
    }
    
    public function testShouldCreateAllSiteFolders()
    {
        $this->markTestIncomplete();
    }
    
    public function testInstallerShouldBeValidXML()
    {
        $this->markTestIncomplete();
    }
    
    public function testAllPHPFilesShouldHaveValidSyntax()
    {
        $this->markTestIncomplete();
    }
    
    public function testModelsWithSQLOnlyShouldNotGenerateCode()
    {
        $this->markTestIncomplete();
    }
    
    public function testWidgets()
    {
        $this->markTestIncomplete();
    }
    
    public function testModelsWithPublishedShouldHavePublicationLogin()
    {
        $this->markTestIncomplete();
    }
    
    public function testEntryPointShouldIncludeTablesPathOnAdmin()
    {
        $this->markTestIncomplete();
    }
}
?>