<?php
if ($act == "applycustomizeview") 
{	
	$viewfields	= $_REQUEST['viewfields'];
	$result = array();
	foreach ($viewfields as $value) 
	{ 
		if ( $value == "epoch_timeofcall") 
		{
			$result[$value] = array( 'Date', 1 );		
		}		
		if ( $value == "status" ) 
		{	
			$result[$value] = array( 'QA status', 1 );
		}	
		if ( $value == "dispo" ) 
		{	
			$result[$value] = array( 'Disposition', 1 );
		}		
		if ( $value == "assigned" ) 
		{	
			$result[$value] = array( 'Agent', 1 );	
		}		
		if ( $value == "phone" ) 
		{	
			$result[$value] = array( 'Phone', 1 );
		}		
		if ( $value == "altphone" ) 
		{	
			$result[$value] = array( 'Alt Phone', 1 );		
		}		
		if ( $value == "mobile" ) 
		{			
			$result[$value] = array( 'Mobile', 1 );		
		}		
		if ( $value == "cname" ) 
		{			
			$result[$value] = array( 'Name', 1 );		
		}		
		if ( $value == "cfname" ) 
		{			
			$result[$value] = array( 'Firstname', 1 );		
		}		
		if ( $value == "clname" ) 
		{
			$result[$value] = array( 'Lastname', 1 );
		}		
		if ( $value == "company" ) 
		{	
			$result[$value] = array( 'Company', 1 );
		}		
		if ( $value == "email" ) 
		{	
			$result[$value] = array( 'Email', 1 );		
		}		
		if ( $value == "address1" ) 
		{			
			$result[$value] = array( 'Address1', 1 );		
		}		
		if ( $value == "address2" ) 
		{	
			$result[$value] = array( 'Address2', 1 );
		}		
		if ( $value == "suburb" ) 
		{	
			$result[$value] = array( 'Suburb', 1 );		
		}		
		if ( $value == "city" ) 
		{	
			$result[$value] = array( 'City', 1 );		
		}		
		if ( $value == "state" ) 
		{	
			$result[$value] = array( 'State', 1 );		
		}		
		if ( $value == "country" ) 
		{	
			$result[$value] = array( 'Country', 1 );
		}		
		if ( $value == "zip" ) 
		{	
			$result[$value] = array( 'Postcode', 1 );		
		}		
		if ( $value == "epoch_callable" ) 
		{	
			$result[$value] = array( 'Date Set', 1 );
		}	
	}		
	$projectid 	= $_REQUEST['projectid'];	
	$userid = $_REQUEST['userid'];	
	$permission	= $_REQUEST['permission'];	
	$vfs = json_encode($result);		
	mysql_query("INSERT INTO uiopt SET project_id ='$projectid', user_id = '$userid', config = 'QueuePreviewFields', value = '$vfs'");	
	mysql_query("INSERT INTO uiopt SET project_id ='$projectid', user_id = '$userid', config = 'QueuePreviewEnabled', value = '$permission'");
}
if ($act == "checkboxapplycustomizeview") 
{	
	$viewfields	= $_REQUEST['viewfields'];	
	$result = array();		
	foreach ($viewfields as $value) 
	{	
		if ( $value == "epoch_timeofcall") 
		{	
			$result[$value] = array( 'Date', 1 );		
		}		
		if ( $value == "status" ) 
		{	
			$result[$value] = array( 'QA status', 1 );		
		}		
		if ( $value == "dispo" ) 
		{	
			$result[$value] = array( 'Disposition', 1 );	
		}		
		if ( $value == "assigned" ) 
		{	
			$result[$value] = array( 'Agent', 1 );		
		}		
		if ( $value == "phone" ) 
		{			
			$result[$value] = array( 'Phone', 1 );	
		}		
		if ( $value == "altphone" ) 
		{			
			$result[$value] = array( 'Alt Phone', 1 );		
		}		
		if ( $value == "mobile" ) 
		{	
			$result[$value] = array( 'Mobile', 1 );		
		}		
		if ( $value == "cname" ) 
		{	
			$result[$value] = array( 'Name', 1 );		
		}		
		if ( $value == "cfname" ) 
		{			
			$result[$value] = array( 'Firstname', 1 );		
		}		
		if ( $value == "clname" ) 
		{	
			$result[$value] = array( 'Lastname', 1 );		
		}		
		if ( $value == "company" ) 
		{	
			$result[$value] = array( 'Company', 1 );		
		}		
		if ( $value == "email" ) 
		{	
			$result[$value] = array( 'Email', 1 );		
		}		
		if ( $value == "address1" ) 
		{	
			$result[$value] = array( 'Address1', 1 );	
		}		
		if ( $value == "address2" ) 
		{	
			$result[$value] = array( 'Address2', 1 );	
		}		
		if ( $value == "suburb" ) 
		{	
			$result[$value] = array( 'Suburb', 1 );		
		}		
		if ( $value == "city" ) 
		{			
			$result[$value] = array( 'City', 1 );		
		}		
		if ( $value == "state" ) 
		{	
			$result[$value] = array( 'State', 1 );		
		}		
		if ( $value == "country" ) 
		{	
			$result[$value] = array( 'Country', 1 );	
		}		
		if ( $value == "zip" ) 
		{	
			$result[$value] = array( 'Postcode', 1 );		
		}		
		if ( $value == "epoch_callable" ) 
		{			
			$result[$value] = array( 'Date Set', 1 );	
		}	
	}		
	$projectid 	= $_REQUEST['projectid'];	
	$userid = $_REQUEST['userid'];	
	$vfs = json_encode($result);		
	mysql_query("INSERT INTO uiopt SET project_id ='$projectid', user_id = '$userid', config = 'QueuePreviewFields', value = '$vfs'");
}
?>