
<?php
//set the include path
        $conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
        set_include_path(parse_ini_file($conf[0])['document.root']);


	include_once "bria_conf.php";

	include 'bria_group_content.php';
//	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	include_once "resources/phpmailer/class.phpmailer.php";
	include_once "resources/phpmailer/class.smtp.php";
//if((strtotime(gmdate('Y-m-d')) > $group_content['date']) || (isset($_GET) && $_GET['manual'] == 1)){
	$group_array = $group_content['group_array'];


?>
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
	if(isset($_POST) && count($_POST) > 0){
		$sql = "select count(bria_group) from bria_setting ";
		$sql .= "where domain_uuid='".$_POST['domain_uuid']."'";
		$database = new database;
		$bria_setting_rows = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
		if($bria_setting_rows == 0){
			$bria_setting_uuid = uuid();
			$insert_uuid = $_SESSION['user_uuid'];
			$sql = "insert into bria_setting(bria_setting_uuid,bria_group,domain_uuid,insert_date,insert_user) VALUES('".$bria_setting_uuid."','".$_POST['bria_group']."','".$_POST['domain_uuid']."',now(),'".$insert_uuid."')";
		}else{
			$update_uuid = $_SESSION['user_uuid'];
			$sql = "update bria_setting set bria_group= '".$_POST['bria_group']."',update_date= now(),update_user='".$update_uuid."' where domain_uuid ='".$_POST['domain_uuid']."' ";
		}
                $database = new database;
                $database->execute($sql, $parameters);
                unset($sql, $parameters);
		message::add("Bria Setting Updated Successfully");
		header('Location: briasetting.php');
		exit;

	}
	$v_domain_sql_new = "select domain_name ";
	$v_domain_sql_new .= "from v_domains where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_domain_sql_new_result = $database->select($v_domain_sql_new, $parameters);
	$domain_name = $v_domain_sql_new_result[0]['domain_name'];
	$v_bria_setting_new = "select bria_group ";
	$v_bria_setting_new .= "from bria_setting where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_bria_setting_result = $database->select($v_bria_setting_new, $parameters);
	$bria_group = $v_bria_setting_result[0]['bria_group'];

	if (permission_exists('bria_setting_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//begin the page content
	require_once "resources/header.php";
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	echo "<b>Bria Setting(Domain:".$domain_name.")</b>";
	echo 	"</div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','link'=>'briaprofile.php'.(is_numeric($page) ? '?page='.$page : null)]);
	echo button::create(['type'=>'button','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','style'=>'margin-left: 15px;','onclick'=>'submit_form();']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";
	echo "<br><br>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tbody>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>Domain(Read-Only)</td>";
	echo "<td width='70%' class='vtable' align='left'><input class='formfld' type='text' name='domain_name' maxlength='255' value='".$domain_name."' required='required' readonly><input class='formfld' type='hidden' name='domain_uuid' maxlength='255' value='".$domain_uuid."' required='required' readonly><br></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>Bria Group</td>";
	echo "<td width='70%' class='vtable' align='left'><input class='formfld' type='text' name='bria_group' maxlength='255' value='".$bria_group."' required='required'><br></td>";
	echo "</tr>";
	echo "</tbody>";
	echo "</table>";
	echo "</form>";
	echo "<script>\n";

//hide password fields before submit
	echo "	function submit_form() {\n";
	echo "		hide_password_fields();\n";
	echo "		$('form#frm').submit();\n";
	echo "	}\n";
	echo "</script>\n";

//include the footer
	require_once "resources/footer.php";

?>
<script>
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
<script type="text/javascript" language="JavaScript">
function profile_bria(group) {
		$.ajax({  
			type: "POST",  
			url: "bria_add.php",  
			data: {'group_ajax': group},  
			success: function(res) {  
 			   document.getElementById('sipprofile').innerHTML =res;
			}  
		});
}
</script>
