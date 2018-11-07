<?php
	if (!ui_getOpt($p, 'isChatEnabled'))
	{
	?>
		$maintabpanel=Ext.getCmp('maintabpanel');
		$maintabpanel.hideTabStripItem('chattab');
	<?php		
	}

	if (!ui_getOpt($p, 'isNotesHistoryEnabled'))
	{
	?>
		notes.hidden=true;
	<?php		
	}

	if (!ui_getOpt($p, 'isConferenceTransferEnabled'))
	{
	?>
		$conferencebutton = $("#conferencebutton");
		$conferencebutton.prop('disabled',true);
		$conferencebutton.attr('title','Disabled by Admin');
	<?php		
	}

	if (ui_getOpt($p, 'isManualCallNotificationEnabled'))
	{
	?>
		$callcontainer = $("#callcontainer");
	<?php		
	}


?>