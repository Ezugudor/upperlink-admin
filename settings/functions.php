<?php 
// this file contains all the user defined functions used in this website
function get_file_thumb($file){
	  $fileArray=explode('.', $file);
	  return $fileArray[0].THUMB_SURFIX.".".$fileArray[1];

}

function blockMatch($matchID){   //NOTE: There must be a record before this function is called/runs. Any calling script should check this.
	 global $con;
	// ///////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////   check the MatchID exist before we start    /////////////////////////////
    
    $checkMatchIDExist = $con->prepare('SELECT sender_id,sender_ph_id,currency from matching where match_id = ? LIMIT 1') or die($con->error);
	$checkMatchIDExist->bind_param('s',$matchID) or die($con->error);
	$checkMatchIDExist->execute() or die($con->error);
	//ThisPHID refers to the PHID of THIS expired match. For the cron, we will be looping through a set of expired matches. Here its just ONLY ONE.
	$checkMatchIDExist->bind_result($ThisSender,$ThisPHID,$currency) or die($con->error); 
	$checkMatchIDExist->store_result() or die($con->error);
	$checkMatchIDExist->fetch() or die($con->error);
	

       if($checkMatchIDExist->num_rows > 0){
       	$checkMatchIDExist->close() or die($con->error);
	        $con->begin_transaction();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////          Block PH, User Unpaid Matches           ///////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	             ///////////////////////////////////////////////////////////////////////////////
				////////////////////////     3 unexecuted/unclosed Query    /////////////////////
				 ///////////////////////////////////////////////////////////////////////////////

	        /////////////////////   Block PH   ////////////////////////////
	        $blockSenderPH = $con->prepare("UPDATE ph SET blocked='1',date_blocked = now() WHERE ph_id = ?"); 
			$blockSenderPH->bind_param("s",$ThisPHID);

			/////////////////////   Block User(Sender)   ////////////////////////////
	        $blockUser = $con->prepare("UPDATE user_auth SET user_blocked = '1',date_blocked = now() WHERE user_id = ?"); 
			$blockUser->bind_param("s",$ThisSender);
            
            /////////////////////  Block the Unpaid Match   ////////////////////////////
			$blockMatch = $con->prepare("UPDATE matching SET blocked='1',date_blocked = now() WHERE match_id = ?"); 
			$blockMatch->bind_param("s",$matchID);


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////    create REVERSED PH(Matches that were confirmed before the user and other transactions were blocked)      ///////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                ///////////////////////////////////////////////////////////////////////////////
				////////////////////////     3 unexecuted/unclosed Query    /////////////////////
				 ///////////////////////////////////////////////////////////////////////////////

			   /// get all confirmed by the receiver
			    $conMatch = $con->prepare("SELECT amount,match_id from matching where sender_ph_id = ? and confirmed_by_receiver= '1' and confirmed_by_sender = '1'") or die($con->error);
				$conMatch->bind_param('s',$ThisPHID) or die($con->error);
				$conMatch->execute() or die($con->error);
				$conMatch->bind_result($completedAmount,$completedMatchID) or die($con->error);
				$conMatch->store_result() or die($con->error);
				if($conMatch->num_rows > 0){
					
					// there is, so we loop through
					$TAmount = 0; //Total Amount Completed
					$numConfirmed = $conMatch->num_rows;

					while($conMatch->fetch()){ echo 'mua';
                          $TAmount += $completedAmount;
					}
					
					 $releaseDate = date("Y-m-d H:i:s",time()+3600); //release time is in the next 1 hr
                     
                     // Generate new unique ID for the PH
                      while (true) {
				            $newPHID = uniqid("PH");

							$checkNewPHID = $con->prepare("SELECT sn FROM ph WHERE ph_id = ?") or die($con->error); 
							$checkNewPHID->bind_param("s",$newPHID)  or die($con->error);
							$checkNewPHID->execute()  or die($con->error);
							$checkNewPHID->bind_result($exist)  or die($con->error);
							$checkNewPHID->store_result()  or die($con->error);
						
						// create the reversed PH Now
						//check if query returns any result
						   if($checkNewPHID->num_rows == 0){
							     $checkNewPHID -> fetch();
							     $checkNewPHID -> close();

								 $PHQuery=$con->prepare("INSERT into ph (ph_id,init_amount,final_amount,balance_ALW,currency,user_id,release_date,no_to_help,no_to_help_confirmed,balance,reversed,reversed_from,date_reversed) values(?,?,?,?,?,?,?,?,?,'0','1',?,now())") or die($con->error); //balance is 0 because this has been completed
								 $PHQuery->bind_param("ssssssssss",$newPHID,$TAmount,$TAmount,$TAmount,$currency,$_SESSION['user_id'],$releaseDate,$numConfirmed,$numConfirmed,$ThisPHID)  or die($con->error);
					             
					             ////////////////////////   Log this activity   //////////////////////////////////////////////////////
								 $logActivity=$con->prepare("INSERT into sys_activity_log(act_id,initiated_by,trans_id) values('2',?,?)");	
								 $logActivity->bind_param("ss",$_SESSION['user_id'],$newPHID);
		                        

		                        // and then break the while loop when you are done
								 break;
							}
						}

						///Update the individual records with the latest PHID i.e newPHID
						$conMatch->data_seek(0);  //return the pointer to 0
						while($conMatch->fetch()){ echo 'me'; 
	                          /////////////////////   update   ////////////////////////////
						// This update must always return affected rows since $conMatch has been checked NOT EMPTY above. so we check for affected rows before commiting or rolling back
						        $updateComp = $con->prepare("UPDATE matching SET sender_ph_id=? WHERE match_id = ?"); 
								$updateComp->bind_param("ss",$newPHID,$completedMatchID);
								$updateComp->execute();
						}

						
                    
				}else{
					//He has not completed anyone before the expire
					// echo "hasnt";
				}
                
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////    Handle Receiver's REFUND (all those matches that the receiver didnt receive but have expired)      //////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				  ///////////////////////////////////////////////////////////////////////////////
				////////////////////////     2 unexecuted/unclosed Query    /////////////////////
				 ///////////////////////////////////////////////////////////////////////////////

				// get all MATCHED to this user BUT NOT confirmed by the receiver  wether EXPIRED or NOT
			    $notConMatch = $con->prepare("SELECT match_id,amount,receiver_gh_id from matching where sender_id = ? and confirmed_by_receiver <> '1' and confirmed_by_sender <> '1' and receiver_refunded <> 1") or die($con->error); // Added 5Seconds extra so that NOW() will be greater than date_to_expire . Because at the time(instantenous) of executiing this script, NOW() is equal to date_to_expire and not greater than. PS: removed " and (now() + INTERVAL 5 SECOND > date_to_expire)" because now we are not only considering this particualr PHID but all he matches that have not just expired but even the ongoing ones. Since the user has been blocked above, all his activities have stopped , so he cant even pay anyoda person. So to avoid someone calling him to pay him whereas he has been blocked, we block/cancel every other transactions/matches attached to him and we refund them.
				$notConMatch->bind_param('s',$ThisSender) or die($con->error);
				$notConMatch->execute() or die($con->error);
				$notConMatch->bind_result($unPaidMatchID,$unpaidAmount,$RxGHID) or die($con->error);
				$notConMatch->store_result() or die($con->error);
				if($notConMatch->num_rows > 0){
				    // echo "ds".$ThisPHID;
					while($notConMatch->fetch()){
	                          /////////////////////   refund Receiver   ////////////////////////////
						// echo "re".$ThisPHID; CASE makes sure the new value doesnt exceed the initial 
						        $refundRX = $con->prepare("UPDATE gh SET balance = CASE WHEN (balance + ?) > init_amount THEN init_amount ELSE (balance + ?) end, no_to_helpme = no_to_helpme - 1 WHERE gh_id = ?"); 
								$refundRX->bind_param("sss",$unpaidAmount,$unpaidAmount,$RxGHID);
								$refundRX->execute();

								/////////////////////  Block the Unpaid Match   ////////////////////////////
								// Note: This particular Block is blocking other transaction attached to thisBlockedUser that have not been confirmed whereas the BlockMatch above blocks this particular match.
								$blockIFNot = $con->prepare("UPDATE matching SET blocked='1',date_blocked = now(),receiver_refunded = '1',date_refunded = now() WHERE match_id = ?"); 
								$blockIFNot->bind_param("s",$unPaidMatchID);
								$blockIFNot->execute();
					}


				}
				
					
				

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////   Check and Execute ( We check for errors here and execute if none or rollback if any. Also send notification if need be)      /////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	        if($conMatch->num_rows > 0 && $notConMatch->num_rows > 0){
	        	if(!$blockSenderPH->execute() || !$blockUser->execute() || !$blockMatch->execute() || !$PHQuery->execute() || !$logActivity->execute() ||$updateComp->affected_rows == 0 || $refundRX->affected_rows == 0 || $blockIFNot->affected_rows == 0 || error_get_last() != NULL){
		        	$err = "sql error : ".$con->error.", php error : ".json_encode(error_get_last());
					echo "{\"success\":\"{$err}\"}";
					$con->rollback();
		        }else{
		        	$con->commit();
		        	
		        	
		        	 $blockSenderPH->close();
		        	 $blockUser->close();
		        	 $blockMatch->close();
		        	 $PHQuery->close();
		        	 $logActivity->close();
		        	 $updateComp->close();
		        	 $blockIFNot->close();
		        	 $refundRX->close();

		        	 echo "{\"success\":\"1\"}";
	            }
	        }else if($conMatch->num_rows > 0 && !$notConMatch->num_rows > 0){
	        	if(!$blockSenderPH->execute() || !$blockUser->execute() || !$blockMatch->execute() || !$PHQuery->execute() || !$logActivity->execute() ||$updateComp->affected_rows == 0 || error_get_last() != NULL){
		        	$err = "sql error : ".$con->error.", php error : ".json_encode(error_get_last());
					echo "{\"success\":\"{$err}\"}";
					$con->rollback();
		        }else{
		        	$con->commit();
		        	
		        	
		        	 $blockSenderPH->close();
		        	 $blockUser->close();
		        	 $blockMatch->close();
		        	 $PHQuery->close();
		        	 $logActivity->close();
		        	 $updateComp->close();

		        	 echo "{\"success\":\"1\"}";
	            }
	        }else if(!$conMatch->num_rows > 0 && $notConMatch->num_rows > 0){
	        	if(!$blockSenderPH->execute() || !$blockUser->execute() || !$blockMatch->execute() || $refundRX->affected_rows == 0 || $blockIFNot->affected_rows == 0 || error_get_last() != NULL){
		        	$err = "sql error : ".$con->error.", php error : ".json_encode(error_get_last());
					echo "{\"success\":\"{$err}\"}";
					$con->rollback();
		        }else{
		        	$con->commit();
		        	
		        	
		        	 $blockSenderPH->close();
		        	 $blockUser->close();
		        	 $blockMatch->close();
		        	 $blockIFNot->close();
		        	 $refundRX->close();

		        	 echo "{\"success\":\"1\"}";
	            }
	        }else if(!$conMatch->num_rows > 0 && !$notConMatch->num_rows > 0){
	        	if(!$blockSenderPH->execute() || !$blockUser->execute() || !$blockMatch->execute() || error_get_last() != NULL){
		        	$err = "sql error : ".$con->error.", php error : ".json_encode(error_get_last());
					echo "{\"success\":\"{$err}\"}";
					$con->rollback();
		        }else{
		        	$con->commit();
		        	
		        	
		        	 $blockSenderPH->close();
		        	 $blockUser->close();
		        	 $blockMatch->close();
		        

		        	 echo "{\"success\":\"1\"}";
	            }
	        }else{
	        	
	        }

	        $conMatch->close();
	        $notConMatch->close();
		}else{
			//the PHID supplied does not exist
		}

}

function notifyAndCommit(){
	    global $con;
	   
	    			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			 		////////////////////          PREPARE the notifications according to users that are set       ///////////////////////////////
			 		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			 		// Notify Referal 
			 		if($GLOBALS['myRefererID'] != NULL){
			 			
				 		$notifyRef=$con->prepare("INSERT into sys_notification(act_id,initiated_by,notified_user,whois_user_to_me) values('2',?,?,'2')");	
				 		$notifyRef->bind_param("ss",$GLOBALS['userID'],$GLOBALS['myRefererID']);
				 	 }

				 	 // Notify Guider
				    if($GLOBALS['myGuiderID'] != NULL){
				 		
				 		$notifyGuider=$con->prepare("INSERT into sys_notification(act_id,initiated_by,notified_user,whois_user_to_me) values('2',?,?,'3')");	
				 		$notifyGuider->bind_param("ss",$GLOBALS['userID'],$GLOBALS['myGuiderID']);
			 		
			 		}

			 		// Notify Guider Guider
				    if($GLOBALS['myGGID'] != NULL){
				 		
				 		$notifyGG=$con->prepare("INSERT into sys_notification(act_id,initiated_by,notified_user,whois_user_to_me) values('2',?,?,'4')");	
				 		$notifyGG->bind_param("ss",$GLOBALS['userID'],$GLOBALS['myGGID']);
			 		
			 		}



			 		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			 		////////////////////          We now EXECUTE and COMMIT according to the ones set above       ///////////////////////////////
			 		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


			 		if($GLOBALS['myRefererID'] != NULL && $GLOBALS['myGuiderID'] == NULL && $GLOBALS['myGGID'] == NULL){ // only referer value set

							if(!$notifyRef->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyRef->close();
							 	    $PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] == NULL && $GLOBALS['myGuiderID'] != NULL && $GLOBALS['myGGID'] == NULL){ //only Guider set

							if(!$notifyGuider->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyGuider->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] == NULL && $GLOBALS['myGuiderID'] == NULL && $GLOBALS['myGGID'] != NULL){ //only GG set

							if(!$notifyGG->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyGG->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] != NULL && $GLOBALS['myGuiderID'] != NULL && $GLOBALS['myGGID'] == NULL){ //referer and Guider set

							if(!$notifyRef->execute() || !$notifyGuider->execute() ){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyRef->close();
							 	$notifyGuider->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] != NULL && $GLOBALS['myGuiderID'] == NULL && $GLOBALS['myGGID'] != NULL){ // referer and GG set

							if(!$notifyRef->execute() || !$notifyGG->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyRef->close();
							 	$notifyGG->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] == NULL && $GLOBALS['myGuiderID'] != NULL && $GLOBALS['myGGID'] != NULL){ // Guider and GG set

							if(!$notifyGuider->execute() || !$notifyGG->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyGuider->close();
							 	$notifyGG->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] != NULL && $GLOBALS['myGuiderID'] != NULL && $GLOBALS['myGGID'] != NULL){ // ALL (Referer , Guider and GG) set

							if(!$notifyRef->execute() || !$notifyGuider->execute() || !$notifyGG->execute()){
							 	    
							 	    $err = $con->error;
							 	    echo "{\"success\":\"{$err}\"}";
							 	    $con->rollback();
							 }else{ 
							 	$notifyRef->close();
							 	$notifyGuider->close();
							 	$notifyGG->close();
							 		
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 }
					  }else if($GLOBALS['myRefererID'] == NULL && $GLOBALS['myGuiderID'] == NULL && $GLOBALS['myGGID'] == NULL){ // NONE (Referer , Guider and GG) set
							 		
							 		//Just commit . we have already checked for error(s) in the ph_insert page :)
									$PHTime = date("d - m - Y h:i a");
							 		$successVar = array('success' => 1,'PHTime' => $PHTime,'PHId' => $GLOBALS['phId'],'PHAmount' => $GLOBALS['amount'],'curr_symbol'=>$GLOBALS['phCurrencySymbol']);
									echo json_encode($successVar);
		 							$con->commit();
							 
					  }
}
function getNotificationCount(){
	    global $con;
	    $notice = $con->prepare("SELECT count(*) FROM sys_notification where notified_user=? AND seen='0'")  or die($con->error); 
		$notice->bind_param('s',$_SESSION['user_id']) or die($con->error);
		$notice->execute()   or die($con->error);
		$notice->bind_result($total) or die($con->error);
		$notice->store_result()  or die($con->error); 		
		$notice->fetch()  or die($con->error);
		// $moneySource->close();
        if($notice->num_rows > 0){
        	return $total;
        }else{
        	return $total;
        }
        $notice->close();
}
function getGuider($refererEmail){
	    global $con;
	    if(strlen($refererEmail) > 0){
	    	// get referals Guider
	    	$referalGuiders = $con->prepare("SELECT guider FROM user_profile where referer=?")  or die($con->error); 
		    $referalGuiders->bind_param('s',$refererEmail) or die($con->error);
		    $referalGuiders->execute();
		    $referalGuiders->bind_result($guiderID);
		    $referalGuiders->store_result();
		    $referalGuiders->fetch();
		    
		    if($referalGuiders->num_rows > 0){
		    	return $guiderID;
		    }else{
		    	$guiderID = '40';
		    	return $guiderID;
		    }
		    $referalGuiders->close();
		    
	    }else{
	    	// get one random Guider from all the top(admin) guiders
	    		 $allAdminGuiders=$con->prepare("SELECT user_id from guiders where admin = 1");						
				 $allAdminGuiders->execute();
				 $allAdminGuiders->bind_result($guiderID);
				 $allAdminGuiders->store_result();
				 $all_admin_guider_id=[];
				 if($allAdminGuiders->num_rows > 0){
				 	while($allAdminGuiders->fetch()){
						$all_admin_guider_id[]=$guiderID;
					} 
					$rand_key=array_rand($all_admin_guider_id);
					$random_admin_id=$all_admin_guider_id[$rand_key];
					return $random_admin_id;
				}else{
					$random_admin_id = '40';
					return $random_admin_id;
				}
				
				
				$allAdminGuiders->close();

				

	    }
}
function getAllNotifications(){   //Not used again
	    global $con;
	    $notice = $con->prepare('SELECT a.notice_id,a.act_id,a.initiated_by,a.seen,a.date_created,a.trans_id,a.bonus_id,a.old_value,a.new_value,b.firstname FROM sys_notification a left join user_profile b on a.initiated_by = b.user_id where notified_user=?')  or die($con->error); 
		$notice->bind_param('s',$_SESSION['user_id']) or die($con->error);
		$notice->execute()   or die($con->error);
		$notice->bind_result($noticeID,$actID,$initiatorID,$seen,$dateCreated,$transID,$bonusID,$oldValue,$newValue,$initiatorFirstname) or die($con->error);
		$notice->store_result()  or die($con->error); 		
		$notice->fetch()  or die($con->error);
		// $moneySource->close();
        if($notice->num_rows > 0){
        	$allNotifications = '';
        	while($notice->fetch()){
        		switch ($actID) {
        			case 1:                // New Registration 
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion ion-person-add"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">New Registration</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											welcome Mr %s you have been sent an email on the rules and regulations, terms and conditions and how this platfomr works
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
							        <div class="clearfix"></div>	
								</li>
        					',$initiatorFirstname);
        				break;
        			case 2:                // New PH
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion-star"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">Referal Bonus</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											You have new referal bonus. 
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>	
								</li>
        					',$initiatorFirstname);
        				break;
        			case 3:                //change of password 
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion ion-android-lock"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">Change of Password</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											Your password has been updated successfully.
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>	
								</li>
        					');
        				break;
        			case 4:                // change of phone
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion ion-ipad"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">Change of phone Number</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											You have successfully changed your phone no.
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>	
								</li>
        					');
        				break;
        			case 5:                // change of email
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion ion-ios-email"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">Change of email address</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											You have successfully changed your email address.
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>	
								</li>
        					');
        				break;
        			case 6:                // change of Guider
        				$allNotifications .= sprintf('
        						<li style="" class="single-notice-list">
								    <div class="notice-main-icon-cont ">
								    	<i class="ion ion ion-person-stalker"></i>
								    </div>
									<div class="notice-info-cont">
										<h6 class="notice-header">
										   <span class="notice-header-title">Change of Guider</span>
										   <div class="clearfix"></div>
										</h6>
										<div class="notice-desc" style="font-weight: bold">
											You have changed your guider. 
										</div>
										<div class="notice-date" style="">yesterday 2:00pm</div>
										<div class="clearfix"></div>
									</div>
									<div class="clearfix"></div>	
								</li>
        					');
        				break;
        			
        			default:
        				# code...
        				break;
        		}
        	}
        }
        $notice->close();
}
function getDaysInMonthOfDate($date){
	$year = date('Y',strtotime($date));
	$month = date('n',strtotime($date));
	$num = cal_days_in_month(CAL_GREGORIAN, $month, $year); // 31
	return $num;
}
function getNextMonthOfDate($dateCreated){
        $daysInMonth = getDaysInMonthOfDate($dateCreated);
        $secInMonth = $daysInMonth*24*60*60;
        $nextMonth = date('Y-m-d H:i:s',strtotime($dateCreated)+$secInMonth);
        return $nextMonth;
}
function getAmountOnDate($principalAmount,$referenceDate,$date){
	    $datetime1 = new DateTime($referenceDate);
		$datetime2 = new DateTime($date);
		$interval = $datetime1->diff($datetime2);
		$dayOfDate = $interval->format('%a');

		// $dayOfDate = date('j',strtotime($date));
		$percentageGrowthOnDate =  (30 * $dayOfDate)/getDaysInMonthOfDate($referenceDate);
		$currentGain = ($percentageGrowthOnDate/100)*$principalAmount;
		$currentAmount = $currentGain + $principalAmount;
		return round($currentAmount,2);
}

	function get_post_formated_time($dbTime){
	  $totalTime=(time()+3600-strtotime($dbTime));
	  $processHour=$totalTime/3600;
	  $hArray=explode('.', $processHour);
    if($hArray[0]>=1){ 
    	if($hArray[0]>=24 && $hArray[0]<=48){
    		$time = "yestarday by ".date('g:i a',strtotime($dbTime));
    	}else if($hArray[0]>48){
    		$time = "on ".date('l, j M Y',strtotime($dbTime));
    	}else{
    		if($hArray[0] == 1){
    			$time="an hour ago";
    		}else{
    			$time=$hArray[0]." hours ago";
    		}
    		
    	}    
	}else{
		$processMin=$totalTime/60;
		$mArray=explode('.', $processMin);
		if($mArray[0]>=1){  //
			if($mArray[0] == 1){
				$time="a min ago";
			}else{
				$time=$mArray[0]." mins ago";
			}
	      
	     }else{
	     	if($totalTime == 1){
	     		$time=$totalTime." sec ago";
	     	}else{
	     		$time=$totalTime." secs ago";
	     	}
	     	
	     }
	}
	return $time;
}
function get_curr_url(){
    $pageUrl="0";
  if($_SERVER['SERVER_PORT']!=80){
     $pageUrl=$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URL'];
  }else{
     $pageUrl=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  }

  return $pageUrl;
}
function get_categories(){
     $get_navbar=mysql_query("select*from category");
	 return $get_navbar;
}

function mysql_prep($input){
   $magic_quotes_active=get_magic_quotes_gpc();
   $new_enough_php=function_exists("mysql_real_escape_string");
   if($new_enough_php){
      if($magic_quotes_active){
	     $input=stripslashes($input);          //remove any effects made by get_magic_quotes_gpc and let mysql_real_escape_string do the work
	     $input=mysql_real_escape_string($input);}
	  }else{                                   //if not new php then check if get_magic_quotes_gpc is active. if not, add slashes manually.
	     if(!$magic_quotes_active){
		     $input=addslashes($input);
		 }
	  }
	  return htmlentities($input,ENT_QUOTES);
}

function redirect_to($location=NULL){
    if($location != NULL){
     header("Location:{$location}");
	 exit;
	}
}


function logout(){
     session_start();
	 $_SESSION=array();
	 if(isset($_COOKIE[session_name()])){
	     setcookie(session_name(),'',time()-4200,'/');
	 }
	 session_destroy();
	 redirect_to('login.php');
}
function get_url_title($raw_postTitle,$pageId){
     $raw_postTitle_pageId=array($raw_postTitle,$pageId);
     $raw_url_title=implode($raw_postTitle_pageId,' ');
     $pattern=array('/[^a-zA-Z0-9-]/','/-/');
	 $replace=array('-','-');
	 $not_pure=preg_replace($pattern,$replace,$raw_url_title);
	 $pure=preg_replace('/-+/','-',$not_pure);
	return $pure;
}


?>