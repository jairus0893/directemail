<a href="#" onclick="um_newteam()" class="jbut">Create Team</a>
<table id="userTeams" class="datatabs" style=" background-color:#FFFFFF;">
		<thead >
			<tr>
				<th class="tableheader">&nbsp;</th>
				<th class="tableheader" >Team Name</th>
				<th class="tableheader">Campaigns</th>
                                <th class="tableheader">Members</th>
				<th class="tableheader">Action</th>
				
			</tr>
		</thead>
		<tbody>
		<?php foreach($alist as $team): 
                    
                    ?>
			<tr>
				<td class="datas"><input type="checkbox"></td>
				<td class="dataleft"><?php echo $team['teamname'] ?></td>
                        <td class="dataleft"><a href="#" onclick="um_teamcampaigns('<?=$team['teamid'];?>')">Team Campaigns</a></td>
                        <td class="dataleft"><a href="#" onclick="um_teammembers('<?=$team['teamid'];?>')">Team Members</a></td>
		<td class="dataleft"><a href="#" onclick="um_updateteam('<?php echo $team['teamid'] ?>')">Edit</a> | <a href="#" onclick="um_deleteteam('<?php echo $team['teamid'] ?>')">Delete</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>