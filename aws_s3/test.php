echo '<?php $salt = "random-salt-goes-here";$password = "Admin@123"; echo md5($salt.$password)."\n"; ?>' > /tmp/test.php


update v_users set password = '0e7517141fb53f21ee439b355b5a1d0a', salt = 'r' where username = 'harsh';


