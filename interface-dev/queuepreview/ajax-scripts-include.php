<script>
function createdateinput_()

	{

	jQuery('#datetd').show();
	jQuery('#dodisposeapplydate').hide();
          $("#accordion").accordion("resize");

	}

function cleardateinput_()

	{

	jQuery('#datetd').hide();
	jQuery('#dodisposeapplydate').hide();
        $("#accordion").accordion("resize");

        

	}
	
function qptdial(led)

{	
	astatus = 'prevlead';
	if (cbable)

    {
        if (astatus == 'dialingcb')

		{

			alert("Hangup the Call first!");

		}

        var thi = document.getElementById('disposition').selectedIndex;

		var sId = document.getElementById('leadid').value;

        if (thi == 0 && sId !=0 && astatus != 'cbview')

		{

			var t =  Ext.ComponentMgr.get("maintabpanel");

            t.activate(0);

            dispose(cbdial,led);

		}

        else {
			if (astatus == 'paused' || astatus =='hanged' || astatus == 'preview' || astatus == 'cbview' || astatus == 'newlead' || astatus == 'prevlead')
			
			{
		
				var uId = '<?=$userid;?>';astatus = 'prevlead';
			
				submitter("getsearchdetails&user="+uId+"&leadid="+led, function(resp){
			
	                    enableb('dbb');
	
	                    showb('nbb');
	
	                    try {
	
	                    hideb('start');
	
	                    }
	
	                    catch (e)
	
	                    {
	
	                    }
	
	                    checkingnew = 21;
	
	                    Ext.getCmp('maintabpanel').activate(0);
	
	                    populate(resp);
			
				});
		
			}

		}

	}

}
</script>