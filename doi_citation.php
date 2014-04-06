<?php

$doi = (isset($_GET["doi"]) && $_GET["doi"] != "" ? $_GET["doi"] : "10.1037/0022-3514.65.6.1190");
$debug = (isset($_GET["debug"]) ? true : false);

function doi_url($doi)
{
  return "http://dx.doi.org/" . $doi;
  //return "http://data.crossref.org/" . $doi;
}

function get_curl($url) 
{ 
  $curl = curl_init(); 
  $header[0] = "Accept: application/rdf+xml;q=0.5,"; 
  $header[0] .= "application/vnd.citationstyles.csl+json;q=1.0"; 
  curl_setopt($curl, CURLOPT_URL, $url); 
  curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)'); 
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
  curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com'); 
  curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
  $json = curl_exec($curl); 
  curl_close($curl); 
  return $json; 
}

function get_json_array($json)
{
  return json_decode($json, true);
}

function show_json($json, $debug=false) {
  if ($debug) {
    echo "<p>" . $json . "</p>";
  }
}

function show_json_array($json_array, $debug=false) {
  if ($debug) {
    echo "<pre class='json_array'>";
    print_r($json_array);
    echo "</pre>";
  }
}

function get_ama_citation($json_array, $doi, $doi_url)
{
  $title        = $json_array["title"];
  $author_array = $json_array["author"];
  $jtitle       = $json_array["container-title"];
  $pages        = $json_array["page"];
  $volume       = $json_array["volume"];
  $issue        = $json_array["issue"];
  $issn_array   = $json_array["ISSN"];
  $url          = $json_array["URL"];
  $year         = $json_array["issued"]["date-parts"][0][0];

  $citation  = "";
  foreach ($author_array as $author)
  {
    $family = $author[family];
    $given = ($author[given] ? $author[given] : "");
    $given = str_replace(".", "", $given);
    $x = explode(" ", $given);
    $given = "";
    foreach ($x as $initial)
    {
      $given .= $initial[0];
    }
    $given = (strlen($given)>0 ? " " . $given : "");
    $authors[] = $family . $given;
  }
  $author_count = count($authors);
  if ($author_count <= 6)
  {
    if (trim($authors[$author_count-1]) != "et al") { 
      $citation .= implode(", ", $authors) . ". ";
    } else {
      for($i=0; $i<3; $i++) {
        $author_list[] = $authors[$i];
      }
      $author_list[] = "et al";
      $citation .= implode(", ", $author_list) . ". ";
    }
  } else {
    $current_author = 0;
    foreach($authors as $author)
    {
      if ($current_author < 3)
      {
        $author_list[] = $author;
        $current_author++;
      }
    }
    $author_list[] = "et al";
    $citation .= implode(", ", $author_list) . ". ";
  }
  $citation .= trim(str_replace(".", "", $title)) . ". ";
  $citation .= trim(str_replace(".", "", $jtitle)) . ". ";
  $citation .= $year;
  $citation .= ($volume ? ";" . $volume : "");
  $citation .= ($issue ? "(" . $issue . ")" : "");
  $citation .= ($pages ? ":" . $pages . ". " : ". ");
  $citation .= ($doi ? "doi:&nbsp;<a href='" . $doi_url . "'>" . $doi . "</a>" : "");
  return $citation;
}

function get_chicago_citation($json_array, $doi, $doi_url)
{
  $title        = $json_array["title"];
  $author_array = $json_array["author"];
  $jtitle       = $json_array["container-title"];
  $pages        = $json_array["page"];
  $volume       = $json_array["volume"];
  $issue        = $json_array["issue"];
  $issn_array   = $json_array["ISSN"];
  $url          = $json_array["URL"];
  $year         = $json_array["issued"]["date-parts"][0][0];

  $citation  = "";
  $author_count = count($author_array);
  $last = $author_count - 1;

  $author_list[] = trim($author_array[0]["family"]) . ", " . trim($author_array[0]["given"]);
  for($i=1; $i<$last; $i++)
  {
    $author_list[] = trim($author_array[$i]["given"]) . " " . trim($author_array[$i]["family"]);
  }
  $author_list[] = "and " . trim($author_array[$last]["given"]) . " " . trim($author_array[$last]["family"]);
  $citation .= implode(", ", $author_list) . ". ";

  $citation .= "&ldquo;" . trim(str_replace(".", "", $title)) . ".&rdquo; ";
  $citation .= "<em>" . trim(str_replace(".", "", $jtitle)) . "</em> ";
  $citation .= ($volume ? $volume : "");
  $citation .= ($issue ? ", no. " . $issue : "");
  $citation .= " (" . $year . ")";
  $citation .= ($pages ? ": " . $pages . ". " : ". ");
  $citation .= ($doi ? "doi:&nbsp;<a href='" . $doi_url . "'>" . $doi . "</a>" : "");

  return $citation;
}

function get_chicago_footnote($json_array, $doi, $doi_url)
{
  $title        = $json_array["title"];
  $author_array = $json_array["author"];
  $jtitle       = $json_array["container-title"];
  $pages        = $json_array["page"];
  $volume       = $json_array["volume"];
  $issue        = $json_array["issue"];
  $issn_array   = $json_array["ISSN"];
  $url          = $json_array["URL"];
  $year         = $json_array["issued"]["date-parts"][0][0];

  $citation  = "";
  $author_count = count($author_array);
  $last = $author_count - 1;

  $author_list[] = trim($author_array[0]["given"]) . " " . trim($author_array[0]["family"]);
  if($author_count < 4)
  {
    for($i=1; $i<$last; $i++)
    {
      $author_list[] = trim($author_array[$i]["given"]) . " " . trim($author_array[$i]["family"]);
    }
    $author_list[] = "and " . trim($author_array[$last]["given"]) . " " . trim($author_array[$last]["family"]);
  } else {
    $author_list[] = "et al";
  }
  $citation .= implode(", ", $author_list) . ", ";

  $citation .= "&ldquo;" . trim(str_replace(".", "", $title)) . ".,&rdquo; ";
  $citation .= "<em>" . trim(str_replace(".", "", $jtitle)) . "</em> ";
  $citation .= ($volume ? $volume : "");
  $citation .= ($issue ? ", no. " . $issue : "");
  $citation .= " (" . $year . ")";
  $citation .= ($pages ? ": " . $pages . ". " : ". ");
  $citation .= ($doi ? "doi:&nbsp;<a href='" . $doi_url . "'>" . $doi . "</a>" : "");

  return $citation;
}


$doi_url      = doi_url($doi);
$json         = get_curl($doi_url);
$json_array   = get_json_array($json);

$ama_citation     = get_ama_citation($json_array, $doi, $doi_url);
$chicago_citation = get_chicago_citation($json_array, $doi, $doi_url);
$chicago_footnote = get_chicago_footnote($json_array, $doi, $doi_url);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DOI Citation Generator</title>
<style>
  * { -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; } 
  html { font-size: 1em; margin: 2em; font-family: Verdana; }
  input[type="text"] { width: 20em; }
  h3 { text-indent: 0.75em; font-weight: normal; }
  .ama, .chicago { max-width: 65%; }
  .nav { max-width: 25%; float: right; font-size: 0.85em; }
  .hangingindent { margin-left: 1em; padding-left: 2em ; text-indent: -2em ; }
  .json_array {  }
  .cf:before, .cf:after { content: " "; display: table; }
  .cf:after { clear: both; }
  .cf { *zoom: 1; }
</style>
</head>
<body>

<form method="get" action="">
<input type="text" name="doi" value="<?php echo $doi ?>">
<input type="submit" value="Search DOI">
<input type="checkbox" name="debug" <?php if (isset($_GET["debug"])) echo " checked"; ?>>debug
</form>

<div class="nav">
  <h2>Examples</h2>
  <div><a href="?doi=10.1037%2F0022-3514.65.6.1190<?php 
    if (isset($_GET["debug"])) echo "&amp;debug=on"; 
    ?>">10.1037/0022-3514.65.6.1190</a></div>
  <div><a href="?doi=10.1016%2Fj.jip.2014.01.003<?php 
    if (isset($_GET["debug"])) echo "&amp;debug=on"; 
    ?>">10.1016/j.jip.2014.01.003</a></div>
  <div><a href="?doi=10.1155%2F2014%2F683757<?php 
    if (isset($_GET["debug"])) echo "&amp;debug=on"; 
    ?>">10.1155/2014/683757</a></div>
  <div><a href="?doi=10.1016%2Fj.quaint.2013.12.014<?php 
    if (isset($_GET["debug"])) echo "&amp;debug=on"; 
    ?>">10.1016/j.quaint.2013.12.014</a></div>
</div>

<section class="cf">
<h2>AMA Citation Format</h2>
<h3>Reference Format</h3>
<ol class="ama">
  <li><?php echo $ama_citation . "\n"; ?></li>
</ol>
<h2>Chicago Citation Format</h2>
<h3>Reference Format</h3>
<p class="chicago hangingindent">
<?php echo $chicago_citation . "\n"; ?>
</p>
<h3>Footnote Format</h3>
<ol class="chicago">
  <li><?php echo $chicago_footnote . "\n"; ?></li>
</ol>
</section>

<?php show_json_array($json_array, $debug); ?>

</body>
</html>
