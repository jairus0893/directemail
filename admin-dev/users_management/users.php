<input type="button" value="Create New" onclick="dialogwindow('newusers')" class="jbut">
<input type="button" value="Import from CSV" onclick="importdata()" class="jbut">
<table id="userList" class="datatabs">
	<thead >
		<tr>
                        <th class="tableheader"><input type="checkbox" id="checkall" onclick="togglecheckall()"></th>
			<th class="tableheader">Name</th>
			<th class="tableheader">Userlogin</th>
                        <th class="tableheader">Teams</th>
			<th class="tableheader">Roles</th>
			<th class="tableheader">Status</th>
                        <th class="tableheader">LastLogin</th>
                        <th class="tableheader">Last 100 Calls</th>
			<th class="tableheader">Action</th>
		
		</tr>
	</thead>
	<tbody>
	<?php foreach ($alist as $value): 
            $c++;
            $class = (($c % 2) > 0) ? 'tableitems':'tableitems_';
            $activetoggle = $value['active'] == 1 ? '<a href="#" onclick="um_deactivate(\''.$value['userid'].'\')">Deactivate</a>' : '<a href="#" onclick="um_activate(\''.$value['userid'].'\')">Activate</a>';
            ?>
		<tr class="<?=$class;?>">
                        <td><input type="checkbox" class="userids" name="userids[]" value="<?php echo $value['userid'];?>" /></td>
			<td><a href="#" onclick="um_edituser('<?=$value['userid'];?>')"><?php echo $value['afirst'].' '. $value['alast']; ?></a></td>
                        <td><?php echo $value['userlogin']; ?> </td>
			<td><?php 
                        if ($value['agent_portal'] == 1)
                        {
                        ?><a href="#" onclick="um_showteams('<?php echo $value['userid'];?>')">User Teams</a></td><?php
                        }
                        else echo 'noTeams';
                            ?>
			<td><?php echo $value['role_name'] ?></td>
			<td><?php echo $value['active'] == 1 ? 'Active' : 'Inactive';?></td>
                        <td><?php echo $value['lastlogin'] > 0 ? date('Y-m-d H:i:s',$value['lastlogin']):'No Data'; ?></td>
			<td>
                        <?php
                        if ($value['agent_portal'] == 1)
                        {
                        ?><a href="#" onclick="um_showhistory('<?=$value['userid'];?>')">View</a><?php
                        }
                        else echo 'noCalls';
                        ?>
                        </td><td><?=$activetoggle;?> | <a href="#" onclick="deleteuser('<?=$value['userid'];?>')" >Delete</a></td>
			
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
