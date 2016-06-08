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
    <?php echo $_SERVER['PHP_SELF'] ?>

</p>
<p>

</p>
<?php

echo phpinfo();

?>
</body></html>