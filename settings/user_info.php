<?php 
    $my_details=$con->prepare("SELECT d.can_ph,d.can_gh,d.match_ph,d.match_gh,d.ph_grows,d.bonus_grows,d.can_change_guider,d.user_blocked,d.username,d.admin,d.privilege,d.date_added,a.surname,a.firstname,a.middlename,a.DOB,a.phoneNo,a.email,a.gender,a.referer,a.guider,a.g_status,b.total_guiding as guider_rank,b.GG,c.total_guiding as GG_rank,ref.surname,ref.firstname,ref.phoneNo,ref.email,gd.surname,gd.firstname,gd.phoneNo,gd.email,gg.surname,gg.firstname,gg.phoneNo,gg.email from user_profile a left join guiders b on a.guider=b.user_id left join guiders c on b.GG = c.user_id left join user_profile ref on a.referer = ref.user_id left join user_profile gd on a.guider = gd.user_id left join user_profile gg on b.GG = gg.user_id left join user_auth d on a.user_id = d.user_id where a.user_id = ?") or die($con->error);
	$my_details->bind_param("s",$_SESSION['user_id']) or die($con->error);
	$my_details->execute() or die($con->error);
	$my_details->bind_result($userCanPH,$userCanGH,$userMatchPH,$userMatchGH,$userPHGrows,$userBonusGrows,$userCanChangeGuider,$userBlocked,$my_username,$my_admin_status,$my_privilege,$my_reg_date,$my_surname,$my_firstname,$my_othernames,$my_dob,$my_phone,$my_email,$my_gender,$myRefererID,$myGuiderID,$myGStatus,$myGuiderRank,$myGGID,$myGGRank,$myRefSurname,$myRefFirstname,$myRefPhone,$myRefEmail,$myGuiderSurname,$myGuiderFirstname,$myGuiderPhone,$myGuiderEmail,$myGGSurname,$myGGFirstname,$myGGPhone,$myGGEmail) or die($con->error);
	$my_details->fetch();
	$my_details->close();


?>


