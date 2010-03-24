<?php
include("xmlparse.php");

if($_GET){
// Replace this with your API Key - you can sign up at http://policeapi.rkh.co.uk/signup/
$key = "ENTER-API-KEY";

$postcode = str_replace(" ", "", $_GET['postcode']);

// This gets the details of the policing team and police force
$getteam = get_xml("http://policeapi.rkh.co.uk/api/geocode-team?key=".$key."&q=". $postcode);

$teamid = $getteam['police-api']['response']['team']['team-id']['value'];
$forceid = $getteam['police-api']['response']['team']['force-id']['value'];

$team = get_xml("http://policeapi.rkh.co.uk/api/team?key=".$key."&force=".$forceid."&team=". $teamid);
$members = get_xml("http://policeapi.rkh.co.uk/api/team-people?key=".$key."&force=".$forceid."&team=". $teamid);

//This gets the crime area ID and crime data from the last 15 months
$getcrimearea = get_xml("http://policeapi.rkh.co.uk/api/geocode-crime-area?key=".$key."&q=". $postcode);

$crimearea = $getcrimearea['police-api']['response']['areas']['area'][3]['area-id']['value'];

$crime = get_xml("http://policeapi.rkh.co.uk/api/crime-area?key=".$key."&force=".$forceid."&area=".$crimearea ."&compare=local");

// This gets the nearest police station
$station = get_xml("http://policeapi.rkh.co.uk/api/nearest-police-station?key=3bca9c7a2e301f130c4e18093dec3ca5&q=ws149sq");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>My Police Information</title>
<link type="text/css" rel="stylesheet" href="http://www.blueprintcss.org/blueprint/src/grid.css" />
<link type="text/css" rel="stylesheet" href="http://www.blueprintcss.org/blueprint/src/typography.css" />
<link type="text/css" rel="stylesheet" href="http://www.blueprintcss.org/blueprint/src/forms.css" />
<link type="text/css" rel="stylesheet" href="style.css" />
</head>
<body>
<div style="width: 90%; margin: 10px auto;">
<h1>My Police Information</h1>
<div style="width: 800px; margin: 0 auto;">
<?php
if ($_GET) {
$totalcrimes = 0;
foreach ($crime['police-api']['response']['total-crimes']['total'] as $crimes) {
$totalcrimes = $totalcrimes + $crimes['value']['value'];
$month = date("M", strtotime($crimes['date']['value']));
$chartlabel1[] = $month;
$year = date("y", strtotime($crimes['date']['value']));
$chartlabel2[] = $year;
$chartnum[] = $crimes['value']['value'];
}
$chartlabel1 = implode("|", $chartlabel1);
$chartlabel2 = implode("|", $chartlabel2);
$max = max($chartnum);
$chartnum = implode(",", $chartnum);
$chart = "http://chart.apis.google.com/chart?chs=360x220&chbh=17,5&cht=bvs&chxt=x,y,x&chxl=0:|".$chartlabel1."|2:|".$chartlabel2."&chd=t:".$chartnum."&chds=0,".$max."&chxr=1,0,".$max.",5";
?>
<h2>Your police team is <strong><a href="<?php echo $team['police-api']['response']['url-force']['value']; ?>"><?php echo $team['police-api']['response']['name']['value']; ?></a></strong></h2>
<p>Crime here is <strong><?php echo str_replace("_", " ", $crime['police-api']['response']['crime-level']['value']); ?></strong> (Compared to the rest of the <?php echo ucwords(str_replace("-", " ", $forceid)); ?> police area) with a total of <?php echo $totalcrimes; ?> recorded crimes in the last 15 months.</p>
<div style="width: 60%; margin: 0 auto;">
<p><?php echo $team['police-api']['response']['description']['value']; ?></p>
</div>
<div style="width: 45%; float: left;">
<h3>Your nearest police station</h3>
<p><strong><?php echo $station['police-api']['response']['stations']['station'][0]['name']['value']; ?></strong><br /><?php echo $station['police-api']['response']['stations']['station'][0]['address']['value']; ?><br /><?php echo $station['police-api']['response']['stations']['station'][0]['postcode']['value']; ?></p>
<p><a href="http://maps.google.co.uk/maps?f=q&source=s_q&hl=en&geocode=&q=<?php echo $station['police-api']['response']['stations']['station'][0]['postcode']['value']; ?>">View on map</a></p>
<h3>Police officers in this team:</h3>
<ul>
<?php 
foreach ($members['police-api']['response']['person'] as $person) {
?>
<li><?php echo $person['rank']['value']; ?> <?php echo $person['name']['value']; ?></li>
<?php } ?>
</ul>
</div>
<div style="width: 45%; float: right;">
<h3>Crimes by month</h3>
<img src="<?php echo $chart; ?>" />
</div>
<?php
} else {
?>
<form action="" method="get" style="text-align: center;">
<p><input type="text" class="title" name="postcode" id="dummy0" onclick="this.value=''" value="Enter your postcode"></p>
<p><button type="submit" class="btn"><span><span>Submit</span></span></button></p>
</form>
<?php } ?>
<div id="powered">
<p>Powered by <a href="http://policeapi.rkh.co.uk/">The Police API</a>.</p>
</div>
</div>
</div>
</body>
</html>