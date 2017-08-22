<?php
require '../RotativaHQ.php';

class BuildPackageTests extends PHPUnit_Framework_TestCase
{
    // ...

    public function test_html_should_return_4_assets()
    {
        // Arrange
        $a = new RotativaHQ('','');
        $a->SetBaseAddress('http://localhost/rhqphp');
        $html = file_get_contents('test.html');

        // Act
        $assets = $a->GetHtmlAssets($html, 'index.html');
        
        // Assert
        $this->assertEquals(5, count($assets));
    }

    public function test_css_should_return_2_assets()
    {
        // Arrange
        $a = new RotativaHQ('','');
        $a->SetBaseAddress('http://localhost/rhqphp');
        $css = file_get_contents('css/test.css');

        // Act
        $assets = $a->GetCssAssets($css, 'test.css');

        // Assert
        $this->assertEquals(1, count($assets));
    }

    // ...
}