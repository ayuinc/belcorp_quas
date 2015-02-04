<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=webservice'.AMP;
?>

<style>
	#changelog, #changelog ul {
		padding-left:20px;
	}
		#changelog ul {
			margin-bottom:10px;
		}
		
	ul {
		list-style-type:disc;
	}
	
	ul,ol {
		margin:0 0 5px 0;
		padding:0 0 0 30px;
	}
	
	h2{
		margin:10px 0 5px 0;
	}
</style>

<div class="clear_left">&nbsp;</div>

<h2>Overview</h2>
Webservice is a module that creates a REST/XMLRPC/SOAP server under the hood of the ExpressionEngine CMS. With this module you are capable to select, insert, update and delete entries with a REST, SOAP or XMLRPC call.
It support the default fields as well custom fields.<br />
So you can for example insert entries from within another application like an Iphone app or another web app.


<h2>Summary of features</h2>
<ol>
	<li>CRUD entries.</li>
	<li>CRUD category.</li>
	<li>Save files through the services</li>
	<li>Error and/or usage Logging (Provided by <a href="http://devot-ee.com/add-ons/omnilog" target="_blank">Omnilog</a>).</li>
</ol>


<h2>Control panel</h2>
<ol>
	<li>Go to the module home</li>
	<li>Click on 'Add member'
		<ol style="padding-left:20px;">
			<li>Select the member</li>
			<li>Select the connection type</li>
			<li>Choose your API(s)</li>
			<li>Is the server active</li>
			<li>Set the logging</li>
			<li>Set the debug mode</li>
		</ol>
	</li>
	<li>Save the channel settings</li>
	<li>Now you can connect with the url (shown on you details page) of the service.</li>
</ol>

<h2>User rights</h2>
All kind of rights and restrictions can as usually be managed in the Control Panel for the membergroups. This module will grab it from there. There will be also a check on the fields that are required.<br/>

<h2>Services documentation</h2>
This can be download <a href="/system/expressionengine/third_party/webservice/client_example/documentation.docx">here</a>.

<h2>The settings</h2>
<p>
	<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
		<thead>
			<tr class="even">
				<th style="width:20%;">Preference</th>
				<th>Setting</th>
			</tr>
		</thead>
		<tr>
			<td>License key</td>
			<td>The license key given by Devot-ee</i></td>
		</tr>
		<tr>
			<td>Debug</td>
			<td>Enable the debug mode</i></td>
		</tr>
	</table>	
</p>

<h2>The settings per member</h2>
<p>
	<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
		<thead>
			<tr class="even">
				<th style="width:20%;">Preference</th>
				<th>Setting</th>
			</tr>
		</thead>
		<tr>
			<td>Member</td>
			<td>Wich user you give access to the services</i></td>
		</tr
		<tr>
			<td>Connection Type</td>
			<td>The type of connection (XMLRPC, REST or SOAP).</td>
		</tr>
		<tr>
			<td>Choose your API(s)</td>
			<td>Enable your API(s)</td>
		</tr>
		<tr>
			<td>Active</td>
			<td>Is the server active or not</td>
		</tr>
		<tr>
			<td>Logging</td>
			<td>What kind of logging needs to be enabled for the services. <b>(<a href="http://devot-ee.com/add-ons/omnilog" target="_blank">Omnilog</a> needs to be installed.)</b></td>
		</tr>
		<tr>
			<td>Debug</td>
			<td>Enable the debug mode</i></td>
		</tr>
		
	</table>	
</p>

</p>

