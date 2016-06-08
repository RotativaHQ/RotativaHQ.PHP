<?php
class HtmlAsset
{
    public $id = '';
    public $content = '';
    
    public function __construct ($id='', $content = '')
    {
        $this->id = $id;
        $this->content = $content;
    }
}

class PdfRequestPayloadV3
{
    public $id = '';
    public $filename = '';
    public $switches = '';
    public $stringAssets = array();
}
?>