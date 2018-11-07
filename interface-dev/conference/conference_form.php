<center><h2>ADD PHONE NUMBER</h2></center><br/>
<center><input id='confcontact' type='text' /></center></br>
<center><h2>CONNECTED PARTIES</h2></center><br/>
<center><select id='confparties' onChange='confparties_change()'><option>None</option></select></center><br/>
<center><h2>TRANSFER PARTY TO <b>AGENT</b></h2></center><br/>
<center><select id='xferto' onChange='xfer_agents()'><option value='None'>Inbound Campaign</option></select>&nbsp;&nbsp;<select id='xferto_agent'><option>Any Agent</option></select></center><br/>
<center><h2>TRANSFER PARTY TO <b>PHONE NUMBER</b></h2></center><br/>");
<center><input id='xferdial' onChange='xferdial_change()' type='text' name='outsideline' value='None'/></br><pre>(Country Code + Phone Number)</pre></center><br/>
