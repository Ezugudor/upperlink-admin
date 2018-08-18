<?php 

     $stmt = $con->prepare("SELECT a.id,a.surname,a.firstname,a.email,a.phone,a.cv,a.passport from user_profile a");
     $stmt->execute();
     $stmt->bind_result($userID,$surname,$firstname,$email,$phone,$resume,$passport);
     
     $resultArr = array('success'=>1);
     while($stmt->fetch()){
           ?>  
           <tr>
             <td><?php printf('%s',$userID); ?></td>
             <td><?php printf('%s',$surname); ?></td>
             <td><?php printf('%s',$firstname); ?></td>
             <td><?php printf('%s',$phone); ?></td>
             <td><?php printf('%s',$email); ?></td>
             <td><?php printf('%s',$resume); ?></td>
             <td><?php printf('%s',$passport); ?></td>
           </tr>


<?php } ?>