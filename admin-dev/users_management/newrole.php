<div class="entryform" style="width:300px; height:350px" title="New List">
                <form name="newrole" id="newrole" >
                <title>Create Custom Role</title>
                <div id="respmessage"></div>
                <div><label>RoleName:</label><input type="text" name="rolename" id="rolename" value="<?=$rol['rolename'];?>" onblur="validate(this,'lengthonly')"/></div>
                <div><label>Admin Portal:</label><select name="admin_portal" id="admin_portal" onchange="rolecall()"><option value="0" <?=$nosel['admin_portal'];?>>No</option><option value="1" <?=$yesel['admin_portal'];?>>Yes</option></select></div>
                <div><label>QA Portal:</label><select name="qa_portal" id="qa_portal"><option value="0" <?=$nosel['qa_portal'];?>>No</option><option value="1" <?=$yesel['qa_portal'];?>>Yes</option></select></div>
                <div><label>Manage Roles:</label><select name="manage_roles" id="manage_roles" class="rc"><option value="0" <?=$nosel['manage_roles'];?>>No</option><option value="1" <?=$yesel['manage_roles'];?>>Yes</option></select></div>
                <div><label>Manage Users:</label><select name="manage_users" id="manage_users" class="rc"><option value="0" <?=$nosel['manage_users'];?>>No</option><option value="1"<?=$yesel['manage_users'];?>>Yes</option></select></div>
                <div><label>Manage Campaigns:</label><select name="manage_campaign" id="manage_campaign" class="rc"><option value="0" <?=$nosel['manage_campaign'];?>>No</option><option value="1" <?=$yesel['manage_campaign'];?> >Yes</option></select></div>
                <div><label>Manage List:</label><select name="manage_list" id="manage_list" class="rc"><option value="0" <?=$nosel['manage_list'];?> >No</option><option value="1" <?=$yesel['manage_list'];?> >Yes</option></select></div>
                <div><label>Manage Client:</label><select name="manage_client" id="manage_client" class="rc"><option value="0" <?=$nosel['manage_client'];?> >No</option><option value="1" <?=$yesel['manage_client'];?> >Yes</option></select></div>
                <div><label>Live Monitor:</label><select name="livemonitor" id="livemonitor" class="rc"><option value="0" <?=$nosel['livemonitor'];?> >No</option><option value="1" <?=$yesel['livemonitor'];?> >Yes</option></select></div>
                <div><label>Reports:</label><select name="reports" id="reports" class="rc"><option value="0" <?=$nosel['reports'];?> >No</option><option value="1"  <?=$yesel['reports'];?> >Yes</option></select></div>
                <div><label>Manage AddOns:</label><select name="manage_addons" id="manage_addons" class="rc"><option value="0"   <?=$nosel['manage_addons'];?> >No</option><option value="1"  <?=$yesel['manage_addons'];?> >Yes</option></select></div>
          <div><label>Chat:</label><select name="chat" id="chat" ><option value="0"  <?=$nosel['chat'];?> >No</option><option value="1"  <?=$yesel['chat'];?> >Yes</option></select></div>
                <?php
                if ($sub == 'editrole')
                {
                ?>
                <div class="buttons">
				<input type="button" value="Update" onclick="updaterole('<?=$roleid;?>')"></div>
          <?php 
                }
                else {
                ?>
                <div class="buttons">
				<input type="button" value="Create" onclick="addnewrole()"></div>
          <?php 
                }
                ?>
                </form>
                </div>
