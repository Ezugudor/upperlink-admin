<?php
//           session_start(); 
        // date_default_timezone_set('Africa/Lagos');
    require_once("../../settings/config.php");
    require_once("../../settings/connect.php");
    
    require_once("../../settings/functions.php");
    // require_once(FULLROOT."settings/user_info.php");

// $_SESSION['user_id'] = 2; //remove this once the session is working


// //error_reporting(E_ALL);

// we first include the upload class, as we will need it here to deal with the uploaded file
include('class.upload.php');

    $supported_img = ['jpg','bmp','gif','png','jpeg'];
    $supported_audio = ['mp3','wma','wav'];
    $is_processed = [];                   

     $title = mysql_prep($_POST['title']); 
     $desc = mysql_prep($_POST['desc']); 
     $category = mysql_prep($_POST['cat']); 
     $expire = mysql_prep($_POST['expire']); 
     $adType = mysql_prep($_POST['ad_type']); 

    /////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////
    /**/   function SQLInsertValue($category,$id){  
            $cs = explode(',', $category);
    /**/     
    /**/     function coma($n){ if(is_numeric($n)){ return $n; }else{ return "'".$n."'"; }  }
    /**/    
    /**/     $cs = array_map('coma', $cs);
    /**/    
    // /**/     $id = $id ? $id : 2222;
    /**/     $process = [];
    /**/     // make the proper string 
    /**/    foreach ($cs as $cat) {
    /**/        $process[]  = "({$id},{$cat})";
    /**/    }
    /**/    // final processing
    /**/    $SQL_insert = implode(',', $process);
    /**/    
    /**/    return $SQL_insert;
          }
    /////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////

     

                           // set variables
    $audio_dest = (isset($_GET['dir']) ? $_GET['dir'] : PATH_AUDIO_FULL);
    $cover_dest = (isset($_GET['thumb_dir']) ? $_GET['thumb_dir'] : PATH_TRACK_COVER_FULL);



     ////////////////////////////////////////////////////////////////////////////////////////////////
     ///////////////////////     IMAGE PROCESSING ZONE - START    ///////////////////////////////////
     ////////////////////////////////////////////////////////////////////////////////////////////////
     // ---------- MULTIPLE UPLOADS ----------

    // as it is multiple uploads, we will parse the $_FILES array to reorganize it into $files
    $files = array();
    foreach ($_FILES['file'] as $k => $l) {
        foreach ($l as $i => $v) {
            if (!array_key_exists($i, $files))
                $files[$i] = array();
            $files[$i][$k] = $v;
        }
    }

    // now we can loop through $files, and feed each element to the class
    foreach ($files as $file) {
           
              $extArr = explode('.',$file['name']);
              $ext = array_pop($extArr);
             
             if(in_array($ext, $supported_audio)){   //process the audio files in this section
                    $audioHandle = new Upload($file);
                     // then we check if the file has been uploaded properly
                     // in its *`".$tt."`* location in the server (often, it is /tmp)
                if ($audioHandle->uploaded) {

                    $fileName = "ADS".uniqid()."U"."T".time();
                    // // image thumb                                    NOTE: always process thumb before the main image as $audioHandle->file_dst_name will have the last processed

                    $audioHandle->file_new_name_body = $fileName; 
                    $audioHandle->Process($audio_dest);

                    // echo $audioHandle->log ;

                    // we check if everything went OK if yes, we save the processed state for audio for later reference when we are done with the other files 
                    if($audioHandle->processed){  $is_processed[] = 'audio'; }

                    
                }

             }else if(in_array($ext, $supported_img)){

                 $coverHandle = new Upload($file);

                    // then we check if the file has been uploaded properly
                    // in its *`".$tt."`* location in the server (often, it is /tmp)
                if ($coverHandle->uploaded) {
                    $fileName = "COVER".uniqid()."U"."T".time();
                    // image thumb                                    NOTE: always process thumb before the main image as $coverHandle->file_dst_name will have the last processed
                    $coverHandle->image_resize            = true;
                    $coverHandle->image_ratio_y           = true;
                    $coverHandle->image_x                 = 700;
                    // M = matching , U = user, T = time
                    $coverHandle->file_new_name_body = $fileName; 
                    $coverHandle->Process($cover_dest);


                    // we check if everything went OK if yes, we save the processed state for audio for later reference when we are done with the other files 
                    if($coverHandle->processed){  $is_processed[] = 'cover'; }

                    
                }

             }
         }


if(in_array('audio', $is_processed) ){

     $user =  isset($_SESSION['user_id'])?$_SESSION['user_id']:2;
     $duration = "12:23:43";
     
            $con->begin_transaction();
            
      if(in_array('cover', $is_processed) ){
            // echo "has cover we are happy";
            $stmt=$con->prepare("INSERT into track(title, file, track_desc, cover, user, duration) values(?,?,?,?,?,?)") or die($con->error);
            $stmt->bind_param("ssssis",$title,$audioHandle->file_dst_name,$desc,$coverHandle->file_dst_name,$user,$duration) or die($con->error);
            $stmt->execute() or die($con->error);



            $d = SQLInsertValue($category,$stmt->insert_id);
            $stmt2=$con->prepare("INSERT into track_genre(track, genre) values $d ") or die($con->error);
            $stmt2->execute() or die($con->error);


            $trackID = $stmt->insert_id;
            $stmt3=$con->prepare("INSERT INTO ads(label,track,date_to_expire,type) VALUES (?,?,?,?)") or die($con->error);
            $stmt3->bind_param("ssss",$desc,$trackID,$expire,$adType) or die($con->error);
            $stmt3->execute() or die($con->error);
    
                     
      }else{
            // echo "no cover but we are still happy";
            $stmt=$con->prepare("INSERT into track(title, file, track_desc, user, duration) values(?,?,?,?,?)") or die($con->error);
            $stmt->bind_param("sssis",$title,$audioHandle->file_dst_name,$desc,$user,$duration) or die($con->error);
            $stmt->execute() or die($con->error);


            $d = SQLInsertValue($category,$stmt->insert_id);
            $stmt2=$con->prepare("INSERT into track_genre(track, genre) values $d ") or die($con->error);
            $stmt2->execute() or die($con->error);


            $trackID = $stmt->insert_id;
            $stmt3=$con->prepare("INSERT INTO ads(label,track,date_to_expire,type) VALUES (?,?,?,?)") or die($con->error);
            $stmt3->bind_param("ssss",$desc,$trackID,$expire,$adType) or die($con->error);
            $stmt3->execute() or die($con->error);
      }

          if($stmt->insert_id > 0 && $stmt2->insert_id > 0 && $stmt3->insert_id > 0){
                     $mainResult = array('success' =>1);
                     echo json_encode($mainResult);
                     $con->commit();
          }else{
                     $mainResult = array('success' =>2);
                     echo json_encode($mainResult);
                     $con->rollback();
          }
        

}else{
             // echo "audio is required";
                $mainResult = array('success' =>4); 
               echo json_encode($mainResult);
}
    
          // // we delete the `".$tt."` files
                $audioHandle-> Clean();
                $coverHandle-> Clean();


?>
