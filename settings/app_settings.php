<?php 
    $systemInfo=$con->prepare("SELECT session_expires,match_expires,ph_matures,gh_matures,referal_bonus,guider_bonus,GG_bonus,main_gain,release_date,site_name,site_title,slogan,contact,gh,ph,match_ph,match_gh,bonus,ph_grows,bonus_grows,hide_match,hide_chat,hide_message,can_change_guider from settings LIMIT 1 ") or die($con->error);
	$systemInfo->execute() or die($con->error);
	$systemInfo->bind_result($systemSessionExpires,$systemMatchExpires,$systemPHMatures,$systemGHMatures,$systemReferalBonus,$systemGuiderBonus,$systemGGBonus,$systemMainGain,$systemReleaseDate,$systemSiteName,$systemSiteTitle,$systemSiteSlogan,$systemSiteContact,$systemGH,$systemPH,$systemMatchPH,$systemMatchGH,$systemBonus,$systemPHGrows,$systemBonusGrows,$systemHideMatch,$systemHideChat,$systemHideMessage,$systemChangeGuider) or die($con->error);
	$systemInfo->fetch();
	$systemInfo->close();
?>