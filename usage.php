<?php
        require 'RotativaHQ.php';
        
        $a = new RotativaHQ();

        $html = file_get_contents('tests/test.html');
        //$image = get_URL('http://cdn1.iconfinder.com/data/icons/love-icons/512/love-heart-128.png');
        $urlPdf = $a->GetPdfUrl($html);
        $resp = json_decode($urlPdf);
        $url = $resp->pdfUrl;
        //header('Location: ' . $url);

        //exit();
?>