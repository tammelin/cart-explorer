<?php

echo $session_id;
$results = Mirakel_Woocommerce_Carts::query_database( $session_id );

?>
<pre>
<?php print_r( $results ); ?>
</pre>