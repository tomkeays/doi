<?php

$doi = (isset($_GET["doi"]) && $_GET["doi"] != "" ? $_GET["doi"] : "10.1037/0022-3514.65.6.1190");
$debug = (isset($_GET["debug"]) ? true : false);
$selected_style = (isset($_GET["style"]) && $_GET["style"] != "" ? $_GET["style"] : "council-of-science-editors");

function doi_url($doi)
{
  return "http://dx.doi.org/" . $doi;
  // return "http://data.crossref.org/" . $doi;
}

function get_curl($url, $selected_style) 
{ 
  $curl = curl_init(); 
  $header[0]  = "Accept: text/x-bibliography; style=" . $selected_style; 
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

function format_citation($citation_string, $selected_style)
{
  $formatted_citation  = "<p class=\"" . $selected_style . "\">";
  $formatted_citation .= $citation_string;
  $formatted_citation .= "</p>";
  return $formatted_citation;
}

$style_array = [
  "american-chemical-society" => "ACS",
  "american-medical-association" => "AMA",
  "apa" => "APA",
  "american-physics-society" => "APS",
  "american-sociological-association" => "ASA",
  "chicago-annotated-bibliography" => "Chicago",
  "council-of-science-editors" => "CSE",
  "ieee" => "IEEE",
  "mla" => "MLA"
];

function select_styles($style_array, $selected_style)
{
  $style_string = "<select name=\"style\">\n";
  foreach ($style_array as $style => $name) {
    $style_string .= "  <option value=\"$style\"";
    if ($selected_style == $style) { $style_string .= " selected"; }
    $style_string .= ">$name</option>\n";
  } 
  $style_string .= "</select>\n";
  return $style_string;
}

$doi_url = doi_url($doi);
$citation_string = get_curl($doi_url, $selected_style);
$formatted_citation = format_citation($citation_string, $selected_style);

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
  ol.chicago li { text-indent: 2em; }
  .json_array {  }
  .cf:before, .cf:after { content: " "; display: table; }
  .cf:after { clear: both; }
  .cf { *zoom: 1; }
</style>
</head>
<body>

<div class="nav">
  <h2>Examples</h2>
  <div><a href="?doi=10.1037%2F0022-3514.65.6.1190<?php 
    echo "&amp;style=$selected_style"; 
    ?>">10.1037/0022-3514.65.6.1190</a></div>
  <div><a href="?doi=10.1016%2Fj.jip.2014.01.003<?php 
    echo "&amp;style=$selected_style"; 
    ?>">10.1016/j.jip.2014.01.003</a></div>
  <div><a href="?doi=10.1155%2F2014%2F683757<?php 
    echo "&amp;style=$selected_style"; 
    ?>">10.1155/2014/683757</a></div>
  <div><a href="?doi=10.1016%2Fj.quaint.2013.12.014<?php 
    echo "&amp;style=$selected_style"; 
    ?>">10.1016/j.quaint.2013.12.014</a></div>
</div>

<h1>DOI &rarr; CSL Citation</h1>
<form method="get" action="">
<input type="text" name="doi" value="<?php echo $doi ?>">
<input type="submit" value="Search DOI">
<?php echo select_styles($style_array, $selected_style); ?>
</form>

<section class="cf">
  <?php echo $formatted_citation . "\n"; ?>
  <p>
    <small>
      Form uses selected <a href="https://github.com/citation-style-language/styles" target="_blank">Citation Style Language</a> style profiles to format citations. 
    </small>
  </p>
</section>

</body>
</html>
