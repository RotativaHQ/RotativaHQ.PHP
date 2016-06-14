<?php

/**
 * RotativaHQ api for PHP
 */
class RotativaHQ
{
    private $sassets = array();
    private $bassets = array();
    private $baseAddress = '';
    private $endpointUrl = '';
    private $apiKey = '';
    private $filename = '';
    private $wkoptions = array();
    private $customSwitches = '';
    
    function __construct($endpointUrl, $apiKey)
    {
        $this->endpointUrl = $endpointUrl;
        $this->apiKey = $apiKey;
        
        if (isset($_SERVER["HTTP_HOST"])) {
            $scheme = $_SERVER["REQUEST_SCHEME"];
            if (isset($scheme) == false) {
                $scheme = 'http';
            }
            $self = $_SERVER['PHP_SELF'];
            $res = $scheme . '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER["SERVER_PORT"] . '/' . substr($self, 1, strrpos($self, '/'));
            $this->baseAddress = $res;
        }
    }

    function SetBaseAddress($baseAddress)
    {
        $this->baseAddress = $baseAddress;
    }
    
    public function SetFilename($filename)
    {
        $this->filename = $filename;
    }

    public function SetCustomSwithes($customSwitches)
    {
        $this->customSwitches = $customSwitches;
    }

    public function SetPageOrientation($orientation)
    {
        array_push($this->wkoptions, "-O ".$orientation);
    }

    public function SetPageWidth($width)
    {
        array_push($this->wkoptions, "--page-width ".$width);
    }

    public function SetPageHeight($height)
    {
        array_push($this->wkoptions, "--page-height ".$height);
    }

    public function SetPageSize($pageSize)
    {
        // A4, A3, ...
        array_push($this->wkoptions, "-s ".$pageSize);
    }

    public function SetPageMargins($pageMarginTop, $pageMarginRight, $pageMarginBottom, $pageMarginLeft)
    {
        array_push($this->wkoptions, "-T ".$pageMarginTop);
        array_push($this->wkoptions, "-R ".$pageMarginRight);
        array_push($this->wkoptions, "-B ".$pageMarginBottom);
        array_push($this->wkoptions, "-L ".$pageMarginLeft);
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
                $imageContent = $this->get_URL($imageRef->src);
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
                $imageContent = $this->get_URL($path);
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

    public function IsLocal($src)
    {
        if (0 === strpos($src, 'http') or 0 === strpos($src, '//')) {
            // It starts with 'http'
            return false;
        }
        return true;
    }

    function get_URL($url)
    {
        $ch = curl_init();

        // Authentication
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //curl_setopt($ch, CURLOPT_USERPWD, 'username:password');
        if ($this->baseAddress == '') {
            throw  new Exception('baseAddress not set');
        }
        $url = rtrim($this->baseAddress, "/") . "/" . ltrim($url, "/");
        // Fetch content as binary data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Fetch binary data
        $imageData = curl_exec($ch);

        curl_close($ch);

        return $imageData;
    }
    
    public function GetPdfUrl($html)
    {
        $payload = new PdfRequestPayloadV3();
        $payload->id = 'dddfdfdfdferr';
        $payload->switches = implode(" ", $this->wkoptions) .' '. $this->customSwitches;
        if ($this->filename != '') {
            $payload->filename = $this->filename;
        }
        $assets = $this->GetHtmlAssets($html, 'index.html');
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
        $encPayload = json_encode($payload);

        $ch = curl_init( $this->endpointUrl. '/v4' );
        // Configuring curl options
        $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Content-type: application/json',
            'X-ApiKey: ' . $this->apiKey
        ) ,
        CURLOPT_POSTFIELDS => $encPayload
        );
        // Setting curl options
        curl_setopt_array( $ch, $options );
        // Getting results
        $result = curl_exec($ch); // Getting jSON result string

        $json = json_decode($result, true);
        if (isset($json)) {
            return $json;
        } else {
            throw new Exception($result);
        }
    }
    
    public function DisplayPDF($html)
    {
        try {
            $resp = $this->GetPdfUrl($html);
            $url = $resp["pdfUrl"];
            header('Location: ' . $url);
        } catch (Exception $ex) {
                echo var_dump($ex->getMessage());
        }

        //echo $url;
        exit();
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

function MapStringAsset($a)
{
    return new WebPageStringAsset($a->originalSrc, $a->id, $a->content);
}
function MapBinAsset($a)
{
    return new WebPageBinAsset($a->originalSrc, $a->id, base64_encode($a->content));
}

?>