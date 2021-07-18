<style>
.subject-info-box-1,
.subject-info-box-2 {
    float: left;
    width: 20%;
}
.subject-info-box-1{
	margin-left: 350px
}
.subject-info-box-1,
.subject-info-box-2 select {
        height: 200px;
        padding: 0;
}.subject-info-box-1,
.subject-info-box-2 select option {
            padding: 4px 10px 4px 10px;
        }
.subject-info-box-1,
.subject-info-box-2 select option:hover {
        }
.subject-info-arrows {
    float: left;
    width: 10%;
}
.subject-info-arrows{
        margin-top: 180px;
    }
.subject-info-arrows input{
        width: 70%;
        margin-top: 5px;
    }
.vtable_new{
    background: black !important;
    color: white !important;
    padding : 2%;
    font-size : 15px;
}
.lstBox1,.lstBox2{
height:500px !important;
}
</style>
<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
*/

//includes
	include_once "bria_conf.php";
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	if (!permission_exists('firewall_view')) {
		echo "access denied"; exit;
	}
	if (count($_POST) > 0) {
		if($_POST['ip_address'] != ""){
			$ip_address =$_POST['ip_address'];
			$output_unblock = shell_exec('sudo /bin/bash /var/www/fusionpbx/app/access_controls/firewall.sh '.$ip_address);
			message::add("IP-Address Is Removed In IPtables.");
			header("Location: firewall_blacklist.php");
			exit;
		}else{
			message::add("Unblock Ip Field Is Required.",'negative');
			header("Location: firewall_blacklist.php");
			exit;
		}
	}
	$document['title'] = "Firewall Blacklist";
	$output = shell_exec('sudo /bin/bash /var/www/fusionpbx/app/access_controls/firewall.sh 1');
	require_once "resources/header.php";
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	echo "<b>Firewall Blacklist(Command:iptables -nvL f2b-sip-auth-failure)</b>";
	echo 	"</div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('firewall_unblock')) {
		echo "<form method='post' name='frm' id='frm'>\n";
		echo "<input class='formfld' type='text' name='ip_address' autocomplete='ip_address' maxlength='255' placeholder='Unblock IP' value='' required='required'>";
		echo button::create(['type'=>'button','label'=>'Unblock IP','icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','style'=>'margin-left: 15px;','onclick'=>'submit_form();']);
		echo 	"</span>\n";
		echo 	"</form>";
		echo "<script>\n";

//hide password fields before submit
		echo "	function submit_form() {\n";
		echo "		hide_password_fields();\n";
		echo "		$('form#frm').submit();\n";
		echo "	}\n";
		echo "</script>\n";
	}
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";
	echo "<br><br>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tbody>";
	if(!empty($output)){
	echo "<tr>";
	echo "<td  width='100%' class='vtable' style='color: white !important;' align='left'>";
	echo "<pre  class='vtable_new'>";
	print_r($output);
	echo "</pre>";
	echo "<br />\n";
	echo "</td>";
	echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";

//include the footer
	require_once "resources/footer.php";

?>
<script>
function password_show_func(flag){
	if(flag == 'Yes'){
		document.getElementById( 'show_password' ).style.display = 'block';
	}else{
		document.getElementById( 'show_password' ).style.display = 'none';
	}
}
(function () {
  $("#btnRight").click(function (e) {
    var selectedOpts = $("#lstBox1 option:selected");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox2").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnAllRight").click(function (e) {
    var selectedOpts = $("#lstBox1 option");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox2").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnLeft").click(function (e) {
    var selectedOpts = $("#lstBox2 option:selected");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox1").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnAllLeft").click(function (e) {
    var selectedOpts = $("#lstBox2 option");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox1").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
})(jQuery);
</script>
