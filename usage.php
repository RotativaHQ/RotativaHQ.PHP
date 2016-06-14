<?php
        require 'RotativaHQ.php';
        
        //$a = new RotativaHQ('http://localhost/rhqphp');

        $a = new RotativaHQ(
            'https://eunorth.rotativahq.com',
            'f57634c2434d41e9b90e5d3d1aef4041'
        );
        //$a = new RotativaHQ('http://7097d369.ngrok.io/v4','f57634c2434d41e9b90e5d3d1aef4041');


        $html = file_get_contents('tests/test.html');

        $a->SetPageOrientation('Landscape');

        $a->SetFilename('ciao.pdf');

        $a->DisplayPDF($html);