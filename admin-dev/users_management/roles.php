<input type="button" value="Create Custom Role" onclick="createnewrole()" class="jbut">
<table id="userRoles" class="datatabs" style=" background-color:#FFFFFF;" width="100%">
		<thead >
			<tr>
				<th class="tableheader">Name</th>
				<th class="tableheader rotate">Agent Portal</th>
                                <th class="tableheader">Admin Portal</th>
                                <th class="tableheader">VIP Portal</th>
                                <th class="tableheader">QA Portal</th>
				<th class="tableheader">Manage Roles</th>
				<th class="tableheader">Manage Users</th>
				<th class="tableheader">Manage Campaigns</th>
				<th class="tableheader">Manage Clients</th>
				<th class="tableheader">Manage List</th>
				<th class="tableheader">Live Monitor</th>
				<th class="tableheader">Reports</th>
				<th class="tableheader">Manage Addons</th>
				<th class="tableheader">Action</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($alist as $roles): ?>
                    
                        <tr>
				<td class="datas"><b><?=$roles['rolename']?></b></td>
				<td class="datas"><?php echo $status = $roles['agent_portal'] == 1 ? 'Yes' : "No"; ?></td>
                                <td class="datas"><?php echo $status = $roles['admin_portal'] == 1 ? 'Yes' : "No"; ?></td>
                                <td class="datas"><?php echo $status = $roles['vip_portal'] == 1 ? 'Yes' : "No"; ?></td>
                                <td class="datas"><?php echo $status = $roles['qa_portal'] == 1 ? 'Yes':"No"; ?></td>
				<td class="datas"><?php echo $status = $roles['manage_roles'] == 1 ? 'Yes' : "No"; ?></td>
			<td class="datas"><?php echo $status = $roles['manage_users'] == 1 ? 'Yes' : "No"; ?></td>
			<td class="datas"><?php echo $status = $roles['manage_campaign'] == 1 ? 'Yes':"No"; ?></td>
				<td class="datas"><?php echo $status = $roles['manage_client'] == 1 ? 'Yes':"No"; ?></td>
				<td class="datas"><?php echo $status = $roles['manage_list'] == 1 ? 'Yes':"No"; ?></td>
				<td class="datas"><?php echo $status = $roles['livemonitor'] == 1 ? 'Yes':"No"; ?></td>
				<td class="datas"><?php echo $status = $roles['reports'] == 1 ? 'Yes':"No"; ?></td>
				
				<td class="datas"><?php echo $status = $roles['manage_addons'] == 1 ? 'Yes':"No"; ?></td>
				<?
                    if ($roles['bcid'] == 0)
                    {
                    ?>
				<td class="datas"></td> 
                     <?
                    }
                    else {
                        ?>
                        <td class="datas"><a href="#" onclick="editrole('<?=$roles['roleid'];?>')">Edit</a> | <a href="#" onclick="deleterole('<?=$roles['roleid'];?>')">Delete</a></td>
                        <?
                    }
                    ?>
                        </tr>
                   <?
                    
                    /*else {
                     
                <tr>
                <td class="datas"><?=$roles['rolename']?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['agent_portal'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['admin_portal'] == 1 ? 'checked="checked"' : ""; ?> </td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['vip_portal'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['qa_portal'] == 1 ? 'checked="checked"' : ""; ?> </td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_roles'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_users'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_campaign'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_client'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_list'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['livemonitor'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['reports'] == 1 ? 'checked="checked"' : ""; ?></td>
                <td class="datas"><input type="checkbox" <?php echo $status = $roles['manage_addons'] == 1 ? 'checked="checked"' : ""; ?></td>

                <td class="datas"><a href="#" onclick="editrole('<?=$roles['roleid'];?>')">Edit</a></td>
                </tr>}*/ 
                   ?>
		<?php endforeach; ?>
		</tbody>
	</table>