<?php

$doi = (isset($_GET["doi"]) && $_GET["doi"] != "" ? $_GET["doi"] : "10.1037/0022-3514.65.6.1190");
$debug = (isset($_GET["debug"]) ? true : false);

function doi_url($doi)
{
  return "http://dx.doi.org/" . $doi;
  // return "http://data.crossref.org/" . $doi;
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
  $citation_string = curl_exec($curl); 
  curl_close($curl); 
  return $citation_string; 
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

function flash_encode($string)
{
  $string = rawurlencode(utf8_encode($string));
  $string = str_replace("%C2%96", "-", $string);
  $string = str_replace("%C2%91", "%27", $string);
  $string = str_replace("%C2%92", "%27", $string);
  $string = str_replace("%C2%82", "%27", $string);
  $string = str_replace("%C2%93", "%22", $string);
  $string = str_replace("%C2%94", "%22", $string);
  $string = str_replace("%C2%84", "%22", $string);
  $string = str_replace("%C2%8B", "%C2%AB", $string);
  $string = str_replace("%C2%9B", "%C2%BB", $string);
  return $string;
}

function get_coins($json_array, $doi, $doi_url)
{
  $atitle       = flash_encode(trim($json_array["title"]));
  $atitle       = str_replace(".", "", $atitle);
  $author_array = $json_array["author"];
  $aulast       = flash_encode(trim($author_array[0]["family"]));
  $aufirst      = flash_encode(trim($author_array[0]["given"]));
  $aufirst      = str_replace(".", "", $aufirst);
  $jtitle       = flash_encode(trim($json_array["container-title"]));
  $jtitle       = str_replace(".", "", $jtitle);
  $pages        = flash_encode($json_array["page"]);
  $volume       = flash_encode($json_array["volume"]);
  $issue        = flash_encode($json_array["issue"]);
  $issn_array   = $json_array["ISSN"];
  $issn         = flash_encode($issn_array[0]);
  $eissn        = flash_encode($issn_array[1]);
  $id           = flash_encode($$json_array["URL"]);
  $year         = $json_array["issued"]["date-parts"][0][0];
  $month        = $json_array["issued"]["date-parts"][0][1];
  $day          = $json_array["issued"]["date-parts"][0][2];
  $doi_encoded  = flash_encode($doi);

  $coins_string  = "ctx_ver=Z39.88-2004";
  $coins_string .= "&rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal";
  $coins_string .= "&rft.genre=article";
  $coins_string .= ($doi     ? "&rft_id=info%3Adoi%2F" . $doi_encoded : "");
  $coins_string .= ($atitle  ? "&rtf.atitle="  . $atitle  : "");
  $coins_string .= ($aulast  ? "&rtf.aulast="  . $aulast  : "");
  $coins_string .= ($aufirst ? "&rtf.aufirst=" . $aufirst : "");
  $coins_string .= ($jtitle  ? "&rtf.jtitle="  . $jtitle  : "");
  $coins_string .= ($volume  ? "&rtf.volume="  . $volume  : "");
  $coins_string .= ($issue   ? "&rtf.issue="   . $issue   : "");
  $coins_string .= ($pages   ? "&rtf.pages="   . $pages   : "");
  $coins_string .= ($issn    ? "&rtf.issn="    . $issn    : "");
  $coins_string .= ($eissn   ? "&rtf.eissn="   . $eissn   : "");
  $coins_string .= ($date    ? "&rtf.date="    . $year    : "");

  $coins_span  = "&lt;span class=\"Z3988\" title=\"";
  $coins_span .= $coins_string;
  $coins_span .= "\"&gt;COinS&lt;/span&gt;";

  return $coins_string;
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


$doi_url      = doi_url($doi);
$json         = get_curl($doi_url);
$json_array   = get_json_array($json);

$coins_string = get_coins($json_array, $doi, $doi_url);
$chicago_citation = get_chicago_citation($json_array, $doi, $doi_url);

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DOI COinS Generator</title>
<style>
  * { -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; } 
  html { font-size: 1em; margin: 2em; font-family: Verdana; }
  input[type="text"] { width: 20em; }
  h3 { text-indent: 0.75em; font-weight: normal; }
  .coins { min-width: 300px; width: 100%; height: 120px; }
  section { max-width: 65%; }
  .hangingindent { margin-left: 1em; padding-left: 2em ; text-indent: -2em ; }
  .nav { max-width: 25%; float: right; font-size: 0.85em; }
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

<section class="main cf">

  <h2>DOI &rarr; COinS Generator</h2>
  <div>
    <textarea class="coins"><span class="Z3988" title="<?php echo $coins_string; ?>">COinS</span></textarea>
  </div>
  <div>
    Example: <span class="Z3988" title="<?php echo $coins_string; ?>">COinS</span>
  </div>
  <p>
    <small>
      Follows the <a href="http://ocoins.info/">COinS</a> specification for encoding <a href="http://ocoins.info/cobg.html">journal articles</a>. To see it in action, you could use <a href="http://www.greasespot.net/">Firefox's Greasemonkey</a> extension and a custom COinS script. Here is one I modified from <a href="http://hublog.hubmed.org/archives/001184.html">Alf Eaton</a> : <a href="openurlcoinssul.user.js">Syracuse University Link Resolver</a>.  
    </small>
  </p>

  <h2>Chicago Citation Format</h2>
  <p class="chicago hangingindent">
  <?php echo $chicago_citation . "\n"; ?>
  </p>
  </section>

  <?php show_json_array($json_array, $debug); ?>

</body>
</html>
