<!DOCTYPE html>
<html><body>
<p>
    <?php echo
        $_SERVER["REQUEST_SCHEME"]
        . '://' .
        $_SERVER['HTTP_HOST']
        . ':' .
        $_SERVER["SERVER_PORT"]
        . $_SERVER['REQUEST_URI']
    ?>

</p>
<p>
    <?php

    $self = $_SERVER['PHP_SELF'];
    $res= $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER["SERVER_PORT"]. '/'.substr($self,1,strrpos($self,'/'));
    echo $res;


    ?>

</p>
<p>

</p>
<?php

echo phpinfo();

?>
</body></html>