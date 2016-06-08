<?php
require './PdfRequestPayload3.php';

$payload = new PdfRequestPayloadV3();
$payload->id = 'dddfdfdfdferr';
$html = '<html><body>Ciao! Beltà perché</body></html>';

array_push($payload->stringAssets, new HtmlAsset('index.html', $html));

$encPayload = bson_encode($payload);

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
$fp = fopen('http://2d8039e3.ngrok.io/v3', 'r', false, $context);
fpassthru($fp);
fclose($fp);

?>