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
    public $binaryAssets = array();
}
/**
 * RotativaHQ api for PHP
 */
class RotativaHQ
{
    private $indexHtml = '';
    private $sassets = array();
    private $bassets = array();
    private $assets = array();
    
    function __construct()
    {
        # code...
    }

    public function GetHtmlAssets($html, $pageName)
    {
        $assets = array();
        $dom = new domDocument;
        $dom->loadHTML($html);
        $dom->preserveWhiteSpace = false;
        $imageRefs = $this->GetImagesAssets($dom);

        foreach (array_unique($imageRefs, SORT_REGULAR) as $imageRef)
        {
            if ($this->IsLocal($imageRef->src)) {
                $suffix = pathinfo($imageRef->src)['extension'];
                $newSrc = uniqid() .'.'. $suffix;
                //$imageContent = file_get_contents($imageRef->src);
                $imageContent = get_URL($imageRef->src);
                //$bincontent =  new MongoBinData($imageContent, MongoBinData::GENERIC);
                $bincontent = $imageContent;
                array_push($assets, new WebPageAsset($imageRef->src, $newSrc, $bincontent, true));
                $html = str_replace($imageRef->src, $newSrc, $html);
            }
        }

        $cssLinks = $dom->getElementsByTagName('link');
        $cssRefs = array();
        foreach ($cssLinks as $css) {
            $src = $css->getAttribute('href');
            $type = 'css';
            array_push($cssRefs, new WebPageAssetRef($src, $type));
        }

        foreach (array_unique($cssRefs, SORT_REGULAR) as $cssRef)
        {
            if ($this->IsLocal($cssRef->src)) {
                $suffix = pathinfo($cssRef->src)['extension'];
                $newSrc = uniqid() .'.'. $suffix;
                $cssContent = file_get_contents($cssRef->src);
                //array_push($assets, new WebPageAsset($imageRef->src, $newSrc, $cssContent, false));
                $cssAssets = $this->GetCssAssets($cssContent, $cssRef->src);
                foreach ($cssAssets as $cssAsset)
                {
                    array_push($assets, $cssAsset);
                    $cssContent = str_replace($cssAsset->originalSrc, $cssAsset->id, $cssContent);
                }
                array_push($assets, new WebPageAsset($cssRef->src, $newSrc, $cssContent, false));
                $html = str_replace($cssRef->src, $newSrc, $html);
            }
        }

        array_push($assets, new WebPageAsset($pageName, $pageName, $html, false));
        return $assets;
    }

    public function GetCssAssets($css, $cssName)
    {
        $assets = array();
        preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $css, $matches, PREG_PATTERN_ORDER);

        $urlRefs = array();
        if ($matches) {
            foreach($matches[3] as $match) {
      // do whatever you want with those matches, adapt paths by changing them to absolute or CDN paths
      // - "images/bg.gif" -> "/path_to_css_module/images/bg.gif"
      // - "images/bg.gif" -> "http://cdn.domain.tld/path_to_css_module/images/bg.gif"
                //echo $match . "<br/>";
                $src = $match;
                $type = 'img';
                array_push($urlRefs, new WebPageAssetRef($src, $type));
            }
        }

        foreach (array_unique($urlRefs, SORT_REGULAR) as $urlRef)
        {
            if ($this->IsLocal($urlRef->src)) {
                $suffix = pathinfo($urlRef->src)['extension'];
                $newSrc = uniqid() .'.'. $suffix;
                $csspath=substr($cssName,0,strrpos($cssName,'/')) . '/';
                $path = normalize_path($csspath . $urlRef->src);
                //$imageContent = file_get_contents($imageRef->src);
                $imageContent = get_URL($path);
                //$bincontent =  new MongoBinData($imageContent, MongoBinData::GENERIC);
                $bincontent = $imageContent;
                array_push($assets, new WebPageAsset($urlRef->src, $newSrc, $bincontent, true));
                $css = str_replace($urlRef->src, $newSrc, $css);
            }
        }

        return $assets;
    }
    
    
    
    public function GetPageStringAssets()
    {
        return $this->sassets;
    }
    public function GetPageBinAssets()
    {
        return $this->bassets;
    }

    public function GetImagesAssets($dom)
    {
        $images = $dom->getElementsByTagName('img');
        $assets = array();
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $type = 'img';
            array_push($assets, new WebPageAssetRef($src, $type));
        }
        return $assets;
    }

    public function GetBinaryContent($src)
    {
        $contents = readfile($src);
        return $contents;
    }

    public function IsLocal($src)
    {
        if (0 === strpos($src, 'http') or 0 === strpos($src, '//')) {
            // It starts with 'http'
            return false;
        }
        return true;
    }
    
    public function SetHtml($html='')
    {
        $this->indexHtml = $html;
        //array_push($this->sassets, new WebPageStringAsset('index.html', $html));
        $dom = new domDocument;
        $dom->loadHTML($html);
        $dom->preserveWhiteSpace = false;
        $imageRefs = $this->GetImagesAssets($dom);
        foreach ($imageRefs as $imageRef)
        {
            if ($this->IsLocal($imageRef->src)) {
                $suffix = pathinfo($imageRef->src)['extension'];
                $newSrc = uniqid() .'.'. $suffix;
                //$imageContent = file_get_contents($imageRef->src);
                $imageContent = get_URL($imageRef->src);
                //$bincontent =  new MongoBinData($imageContent, MongoBinData::GENERIC);
                $bincontent = $imageContent;
                array_push($this->assets, new WebPageAsset($imageRef->src, $newSrc, $bincontent, true));
            }
        }
    }

    public function GetPdfUrl($html)
    {
        $payload = new PdfRequestPayloadV3();
        $payload->id = 'dddfdfdfdferr';
        $assets = $this->GetHtmlAssets($html, 'index.html');
        //$sassets = $this->GetPageStringAssets();
        //array_push($assets, new WebPageAsset('', 'index.html', $this->indexHtml, false));
        $binAssets = array_filter($assets, function($a) {
            return $a->binary == true;
        });
        $strAssets = array_filter($assets, function($a) {
            return $a->binary == false;
        });
        foreach ($binAssets as $binAsset)
        {
            array_push($payload->binaryAssets, MapBinAsset($binAsset));
        }
        foreach ($strAssets as $strAsset)
        {
            array_push($payload->stringAssets, MapStringAsset($strAsset));
        }
        //$payload->binaryAssets = array_map("MapBinAsset", $binAssets);
        //$payload->stringAssets = array_map("MapStringAsset", $strAssets);
        //$encPayload = bson_encode($payload);
        $document = new BSONDocument($payload);
        $encPayload = $document->pack();
        //$encPayload = MongoDB\BSON\fromPHP($payload);
        
        $fp = fopen('data.txt', 'w');
        fwrite($fp, $encPayload);
        fclose($fp);


        $opts = array(
          'http'=>array(
            'method'=>"POST",
            'header'=>"Accept-language: en\r\n" .
                      "Content-type: application/bson\r\n".
                      "Accept: application/json\r\n".
                      "X-ApiKey: f57634c2434d41e9b90e5d3d1aef4041\r\n",
            'content'=>$encPayload
          )
        );

        $context = stream_context_create($opts);

        /* Sends an http request to www.example.com
           with additional headers shown above */
        $fp = fopen('http://c619e71b.ngrok.io/v3', 'r', false, $context);
        fpassthru($fp);
        fclose($fp);
    }
}

/**
 * Web page asset
 */
class WebPageStringAsset
{
    public $id = '';
    public $originalSrc = '';
    public $content = '';
    
    function __construct($originalSrc, $id, $content)
    {
        $this->originalSrc = $originalSrc;
        $this->id = $id;
        $this->content = $content;
    }
}
/**
 * Web page asset
 */
class WebPageBinAsset
{
    public $id = '';
    public $originalSrc = '';
    public $content = '';

    function __construct($originalSrc, $id, $content)
    {
        $this->originalSrc = $originalSrc;
        $this->id = $id;
        $this->content = $content;
    }
}
class WebPageAsset
{
    public $id = '';
    public $originalSrc = '';
    public $content = '';
    public $binary = false;

    function __construct($originalSrc, $id, $content, $binary)
    {
        $this->originalSrc = $originalSrc;
        $this->id = $id;
        $this->content = $content;
        $this->binary = $binary;
    }
}

class WebPageAssetRef
{
    public $src = '';
    public $type = '';

    function __construct($src, $type)
    {
        $this->src = $src;
        $this->type = $type;
    }
}

/**
 * This function is a proper replacement for realpath
 * It will _only_ normalize the path and resolve indirections (.. and .)
 * Normalization includes:
 * - directiory separator is always /
 * - there is never a trailing directory separator
 * @param  $path
 * @return String
 */
function normalize_path($path) {
    $parts = preg_split(":[\\\/]:", $path); // split on known directory separators

    // resolve relative paths
    for ($i = 0; $i < count($parts); $i +=1) {
        if ($parts[$i] === "..") {          // resolve ..
            if ($i === 0) {
                throw new Exception("Cannot resolve path, path seems invalid: `" . $path . "`");
            }
            unset($parts[$i - 1]);
            unset($parts[$i]);
            $parts = array_values($parts);
            $i -= 2;
        } else if ($parts[$i] === ".") {    // resolve .
            unset($parts[$i]);
            $parts = array_values($parts);
            $i -= 1;
        }
        if ($i > 0 && $parts[$i] === "") {  // remove empty parts
            unset($parts[$i]);
            $parts = array_values($parts);
        }
    }
    return implode("/", $parts);
}

function get_URL($url)
{
    $ch = curl_init();

    // Authentication
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
    //curl_setopt($ch, CURLOPT_USERPWD, 'username:password'); 
    
    // Fetch content as binary data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // Fetch image data
    $imageData = curl_exec($ch);
    
    curl_close($ch); 
    
    return $imageData;
}

    function MapStringAsset($a)
    {
        return new WebPageStringAsset($a->originalSrc, $a->id, $a->content);
    }

    function MapBinAsset($a)
    {
        return new WebPageBinAsset($a->originalSrc, $a->id, $a->content);
    }


class BSONDocument {

    const T_ROOT = 1;
    const T_DOCUMENT = 2;
    const T_ARRAY = 3;

    private $name;
    private $type;
    private $children = [];

    public function __construct($data, $name = null) {
        if (is_null($name)) {
            $this->type = self::T_ROOT;
        }
        $this->name = $name;
        switch (gettype($data)) {
            case "array":
                if (!$this->type) {
                    if ($this->isSequential($data)) {
                        $this->type = self::T_ARRAY;
                    } else {
                        $this->type = self::T_DOCUMENT;
                    }
                }
                $this->children = $data;
                break;
            case "object":
                if (!$this->type) {
                    $this->type = self::T_DOCUMENT;
                }
                $this->children = get_object_vars($data);
                break;
            default:
                throw new BSONException("Unexpected document type");
        }
    }

    public function pack() {
        $eList = '';
        if (self::T_ROOT != $this->type) {
            $prefix = "\x03";
            if (self::T_ARRAY == $this->type) {
                $prefix = "\x04";
            }
            $eList = pack('aa*x', $prefix, $this->name);
        }
        foreach ($this->children as $key => $val) {
            if (is_scalar($val)) {
                $element = new BSONScalar($val, $key);
                $eList .= $element->pack();
            } else {
                $element = new BSONDocument($val, $key);
                $packed = $element->pack();
                $eList .=  pack("a*xa*", $key, $packed);
            }
        }
        $len = 4 + strlen($eList);
        $eList = pack("la*", $len, $eList);
        return $eList;
    }

    private function isSequential(array $array) {
        return array_keys($array) == range(0, count($array) - 1);
    }

}

class BSONException extends Exception {
}

class BSONScalar {

    const T_BOOL = 1;
    const T_INT = 2;
    const T_DOUBLE = 3;
    const T_STRING = 4;
    const T_NULL = 5;

    private $type;
    private $name;
    private $value;

    public function __construct($value, $name) {
        $this->name = $name;
        if (is_int($value)) {
            $this->type = self::T_INT;
        } elseif (is_string($value)) {
            $this->type = self::T_STRING;
        } elseif (is_double($value)) {
            $this->type = self::T_DOUBLE;
        } elseif (is_bool($value)) {
            $this->type = self::T_BOOL;
        } elseif (is_null($value)) {
            $this->type = self::T_NULL;
        } else {
            throw new BSONException("Unexpected type");
        }
        $this->value = $value;
    }

    /**
     * @return string
     * @throws BSONException
     */
    public function pack() {
        $ret = null;
        switch ($this->type) {
            case self::T_BOOL:
                $ret = $this->packBool();
                break;
            case self::T_INT:
                $ret = $this->packInt32();
                break;
            case self::T_DOUBLE:
                $ret = $this->packDouble();
                break;
            case self::T_STRING:
                $ret = $this->packString();
                break;
            case self::T_NULL:
                $ret = $this->packNull();
                break;
            default:
                throw new BSONException("Unexpected scalar value type");
        }
        return $ret;
    }

    private function packBool() {
        return pack('aa*xc', "\x08", $this->name, $this->value);
    }

    private function packInt32() {
        //@todo int64 support
        return pack('aa*xV', "\x10", $this->name, $this->value);
    }

    private function packDouble() {
        $val = pack('d', $this->value);
        //long double imitation for x86 machines
        if (4 == PHP_INT_SIZE) {
            $val = $val."\x00\x00";
        }
        return "\x01".$this->name."\x00".$val;
    }

    private function packString() {
        //@todo pack either UTF-8 strings or bin data
        //for now consider all strings as generic bin
        $len = strlen($this->value);
        return pack('aa*xVca*', "\x05", $this->name, $len, "\x00", $this->value);
    }

    private function packNull() {
        return pack('aa*x', "\x0A", $this->name);
    }

}
?>