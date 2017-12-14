<?php

/**
 * @package SimpleSAMLphp
 */

if (!array_key_exists('StateId', $_REQUEST)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$state = SimpleSAML_Auth_State::loadState($_REQUEST['StateId'], 'fingerprinting:request');
$this->includeAtTemplateBase('includes/header-coreen.php');
?>
<?php
if(!isset($_POST['fp'])) {
?> 

<script>
$.extend({
    redirectPost: function(location, args) {
       	var form = '';
       	$.each( args, function( key, value ) {
            form += '<input type="hidden" name="'+key+'" value="'+value+'">';
       	});
       	$('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
    }
});

var d1 = new Date();
var options = {excludePlugins: true, excludeColorDepth: true, excludeJsFonts: true, excludeDoNotTrack: true, excludeCpuClass: true};
var fp = new Fingerprint2(options);

fp.get(function(result, components) {
    var d2 = new Date();
    var timeString = "Time took to calculate the fingerprint: " + (d2 - d1) + "ms";
    var details = "<strong>Detailed information: </strong><br />";
    if(typeof window.console !== "undefined") {

       	var fpObj = {}

       	for (var index in components) {
            var obj = components[index];
            var value = obj.value;
            var line = obj.key + " = " + value.toString().substr(0, 100);
            details += line + "<br />";
            if(obj.key == 'regular_plugins') 
               	fpObj[obj.key] = encodeURIComponent(JSON.stringify(obj.value));
            else
               	fpObj[obj.key] = obj.value
       	}
    
       	$.redirectPost(location.href, {fp: encodeURIComponent(JSON.stringify(fpObj))});
    }
    $("#details").html(details);
    $("#fp").text(result);
    $("#time").text(timeString);
});
</script> 

<?php
    exit;
}
?>

<?php
$this->includeAtTemplateBase('includes/fingers.php');

$postparam = rawurldecode($_POST['fp']);
$array_val = json_decode($postparam, true);

if(isset($array_val['regular_plugins'])) getRegularPlugins($array_val['regular_plugins']);

$hdr_arr = getallheaders();
$array_val['accept'] = getAccept($hdr_arr);
$array_val['accept_encoding'] = getAcceptEncoding($hdr_arr);
$array_val['accept_language'] = getAcceptLanguage($hdr_arr);

$ref_res = getReferenceResolution();

$normalized_res = getNormalizedScreenResolution($ref_res, $array_val['user_agent'], $array_val['resolution'], $array_val['pixel_ratio']);
$array_val['norm_resolution'] = $normalized_res;

if(isset($array_val['language'])) $array_val['norm_language'] = getNormalizedLanguage($array_val['language']);
if(isset($array_val['user_agent'])) {
   $osinfo = getNormalizedOS($array_val['user_agent']);
   $os_ver = $osinfo['os_ver'];
   $os_arch = $osinfo['os_arc'];
   $array_val['norm_osinfo']  = $osinfo;
   $array_val['norm_browser'] = getNormalizedBrowser($array_val['user_agent']);
}

if(isset($array_val['webgl']))
    $array_val['norm_renderer'] = getNormalizedUnmaskedRenderer($array_val['webgl']);
else $array_val['norm_renderer'] = 'null';

if(isset($state)) {
    $array_val['userid'] = getUserId($state);
    $array_val['usereppn'] = getEdupersonPrincipalName($state);
}

$conn = connectSQL();

$query_str = generateSQLQuery($array_val, 'all');
querySQL($conn, $query_str);

$query_gen_str = generateSQLQuery($array_val, 'sub'); 
querySQL($conn, $query_gen_str);

$query_hash_str = generateSQLQuery($array_val, 'hash');
querySQL($conn, $query_hash_str);

releaseSQL($conn);

?>

<form name="fp_complete" action="<?php echo htmlspecialchars($this->data['yesTarget']); ?>">
	<?php
		foreach ($this->data['yesData'] as $name => $value) {
			echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
		}
	?>
	<input type="hidden" name="yes" id="yesbutton" value="gathering completed" />
	<script language="JavaScript">document.fp_complete.submit();</script></form>
</form>

<?php

$this->includeAtTemplateBase('includes/footer-coreen.php');
