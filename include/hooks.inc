<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2007 Anders Lövgren
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// -------------------------------------------------------------------------------

// 
// This file contains functions that you can use to hook in into the script
// execution at various steps to perform tasks as indata validation.
// 

// 
// Maximum number of molecules accepted in each uploaded smiles file:
// 
define ("MAX_SMILES_MOLECULES", 500);

// 
// Validate molecules in indata:
//
define ("VALIDATE_MOLECULES", true);

// 
// Validates molecule format. This function does a simple validation of molecule
// string. We simply checks that the string contains atoms defined in the lookup
// table.
// 
function validate_molecule_smiles($str, &$error)
{   
    $atoms = array(
		   "H"   =>   1, "He"  =>   2,
		   
		   "Li"  =>   3, "Be"  =>   4, "B"   =>   5, "C"   =>   6, "N"   =>   7,
		   "O"   =>   8, "F"   =>   9, "Ne"  =>  10,
		   
		   "Na"  =>  11, "Mg"  =>  12, "Al"  =>  13, "Si"  =>  14, "P"   =>  15,
		   "S"   =>  16, "Cl"  =>  17, "Ar"  =>  18,
		   
		   "K"   =>  19, "Ca"  =>  20, "Sc"  =>  21, "Ti"  =>  22, "V"   =>  23,
		   "Cr"  =>  24, "Mn"  =>  25, "Fe"  =>  26, "Co"  =>  27, "Ni"  =>  28,
		   "Cu"  =>  29, "Zn"  =>  30, "Ga"  =>  31, "Ge"  =>  32, "As"  =>  33,
		   "Se"  =>  34, "Br"  =>  35, "Kr"  =>  36,
		   
		   "Rb"  =>  37, "Sr"  =>  38, "Y"   =>  39, "Zr"  =>  40, "Nb"  =>  41,
		   "Mo"  =>  42, "Tc"  =>  43, "Ru"  =>  44, "Rh"  =>  45, "Pd"  =>  46,
		   "Ag"  =>  47, "Cd"  =>  48, "in"  =>  49, "Sn"  =>  50, "Sb"  =>  51,
		   "Te"  =>  52, "I"   =>  53, "Xe"  =>  54,
		   
		   "Cs"  =>  55, "Ba"  =>  56, "La"  =>  57, "Ce"  =>  58, "Pr"  =>  59,
		   "Nd"  =>  60, "Pm"  =>  61, "Sm"  =>  62, "Eu"  =>  63, "Gd"  =>  64,
		   "Tb"  =>  65, "Dy"  =>  66, "Ho"  =>  67, "Er"  =>  68, "Tm"  =>  69,
		   "Yb"  =>  70, "Lu"  =>  71, "Hf"  =>  72, "Ta"  =>  73, "W"   =>  74,
		   "Re"  =>  75, "Os"  =>  76, "Ir"  =>  77, "Pt"  =>  78, "Au"  =>  79,
		   "Hg"  =>  80, "Tl"  =>  81, "Pb"  =>  82, "Bi"  =>  83, "Po"  =>  84,
		   "At"  =>  85, "Rn"  =>  86,
		   
		   "Fr"  =>  87, "Ra"  =>  88, "Ac"  =>  89, "Th"  =>  90, "Pa"  =>  91,
		   "U"   =>  92, "Np"  =>  93, "Pu"  =>  94, "Am"  =>  95, "Cm"  =>  96,
		   "Bk"  =>  97, "Cf"  =>  98, "Es"  =>  99, "Fm"  => 100, "Md"  => 101,
		   "No"  => 102, "Lr"  => 103, "Rf"  => 104, "Db"  => 105, "Sg"  => 106,
		   "Bh"  => 107, "Hs"  => 108, "Mt"  => 109, "Ds"  => 110, "Rg"  => 111,
		   "Uub" => 112, "Uut" => 113, "Uuq" => 114, "Uup" => 115, "Uuh" => 116,
		   "Uus" => 117, "Uuo" => 118,
		   
		   "h" =>  1, "b" =>  5, "c" =>  6, "n" =>  7, "o" =>  8, "f" =>  9, 
		   "p" => 15, "s" => 16, "k" => 19, "v" => 23, "y" => 39, "i" => 53, 
		   "w" => 74, "u" => 92,
		   );
    
    $match = array();
    $pattern = '/[A-Z][a-z][a-z]|[A-Z][a-z]|[A-Z]|[a-z]/';
    if(preg_match_all($pattern, $str, $match)) {
    	foreach($match[0] as $key) {
    	    if(isset($atoms[$key])) {
    		continue;
    	    } else {
    		$error = "invalid atom $key";
		return false;
	    }
	}
    } else {
	$error = "failed match $str";
	return false;
    }
    
    return true;
}

// 
// Validate molecule names (the string may contain space separated names).
// We don't allow names matching a float point number as they will be
// feeded to Simca-QP and cause a "too many variables" exception.
// 
function validate_molecule_names($str, &$error)
{
    $names = explode(' ', $str);
    array_shift($names);           // skip [d+]
    
    foreach($names as $name) {
	if(preg_match('/^[0-9]+(\.[0-9]+)$/', $name)) {
	    $error = "molecule name can't be a float point number";
	    return false;
	}
    }
    return true;
}

// 
// This function gets called prior to a job being added to the job queue. This 
// allows submitted data and files (in $file) to be validated and rejected if
// its invalid. This function should return true if the file should be accepted. 
// If the file should be rejected, then set error to an string describing the
// cause and return false.
// 
function pre_enqueue_hook($file, $jobdir, &$error)
{
    // 
    // Check MIME content type of uploaded file.
    // 
    $mime = mime_content_type($file);
    if($mime != "text/plain") {
	$error = sprintf("Uploaded file is %s. Only plain text files are accepted.", $mime);
	return false;
    }
    
    // 
    // Make sure number of molecules in smiles file is < max allowed;
    // 
    $str = file_get_contents($file);
    if(substr_count($str, "\n") > MAX_SMILES_MOLECULES) {
	$error = sprintf("The maximum number of molecules that can be uploaded in an smiles file are %d", MAX_SMILES_MOLECULES);
	return false;
    }
    
    // 
    // Prefix the molecule names with their line number in file. This saved us
    // from head ache later when building our database of known good/bad molecules.
    // As a side effect we can also report the line number of bad molecules 
    // when running dragonX along with their names :-)
    //     
    $temp  = sprintf("%s/indata.tmp", $jobdir);
    
    $fsi = fopen($file, "r");
    if(!$fsi) {
	$error = "Failed open input data file (uploaded)";
	return false;
    }
    $fso = fopen($temp, "w");
    if(!$fso) {
	$error = "Failed open input data file (temporary)";
	return false;
    }

    $index = 1;
    while($str = fgets($fsi)) {
    	$match = array();
    	if(preg_match('/^(.*?)\s+(.*)/', trim($str), $match)) {
	    if(VALIDATE_MOLECULES) {
		if(!validate_molecule_smiles($match[1], $error)) {
		    $error = sprintf("Failed validate molecule on line %d (%s)", $index, $error);
		    return false;
		}
		if(!validate_molecule_names($match[2], $error)) {
		    $error = sprintf("The molecule name on line %d is invalid (%s)", $index, $error);
		    return false;
		}
	    }
    	    fwrite($fso, sprintf("%s\t[%d] %s\n", $match[1], $index++, $match[2]));
    	}
    	else { 
	    if(VALIDATE_MOLECULES) {
		if(!validate_molecule_smiles(trim($str), $error)) {
		    $error = sprintf("Failed validate molecule on line %d (%s)", $index, $error);
		    return false;
		}
	    }
    	    fwrite($fso, sprintf("%s\t[%d] ???\n", trim($str), $index++));
    	}
    }
    fclose($fsi);
    fclose($fso);

    if(!rename($temp, $file)) {
	$error = "Failed process uploaded input data file";
	return false;
    }
    
    return true;
}

// 
// This function gets called after the data has been enqueued. The $file
// argument is the input data and $jobdir is the per job directory.
// 
function post_enqueue_hook($file, $jobdir)
{
    // 
    // Create the molecule database structure. We haven't got enough information
    // about which molecules are good and bad, so we can't add them here. We have
    // to wait until chemgps-sqp-prepare.pl has runned before we add them.
    // 
    // The molecule database is organized as:
    // 
    // +-- cache/
    //      +-- db/
    //           +-- incoming/           // not yet classified
    //           |    +-- hostid1        // spool file
    //           |    +-- hostid2        // spool file
    //           |   ...
    //           |    +-- hostidN        // spool file
    //           +-- accepted/           // known good
    //           +-- rejected/           // known bad
    // 
    
    $dbdir = sprintf("%s/db", CACHE_DIRECTORY);
    if(!file_exists($dbdir)) {
    	if(!mkdir($dbdir)) {
    	    error_exit("Failed create smiles database directory (db root)\n");
    	}
	foreach(array( "incoming", "accepted", "rejected" ) as $subdir) {
	    if(!mkdir(sprintf("%s/%s", $dbdir, $subdir))) {
		error_exit(sprintf("Failed create smiles database subdirectory (%s)\n", $subdir));
	    }
	}
    }
    
    // 
    // Append the job directory to hostids spool file. Save some meta data
    // that can be useful later.
    // 
    $spool  = sprintf("%s/incoming/%s", $dbdir, $_COOKIE['hostid']);
    file_put_contents($spool, sprintf("%s\t%s\n", $jobdir, $_SERVER['REMOTE_ADDR']), FILE_APPEND);
}

// 
// This function gets called during collection of statistics data. The data is
// later saved to the directory structure under cache/stat. The hostid argument
// is either "all" or a unique ID of submitter. The jobdir is the directory
// to harvest statistics from. The year, month, day and hour is the time when
// this job got queued (submitted).
// 
function collect_data_hook($hostid, &$data, $jobdir, $year, $month, $day, $hour)
{
    // 
    // The file to collect and the section name we save under.
    // 
    $sect = "dragonx";
    $file = sprintf("%s/dragonx.stats", $jobdir, $sect);
    
    if(file_exists($file)) {
	$handle = fopen($file, "r");
	$dragonx['accepted'] = trim(fgets($handle));
	$dragonx['rejected'] = trim(fgets($handle));
	fclose($handle);
   
        // 
	// Make sure to initilize our section on first use.
	// 
	foreach(array( "accepted", "rejected" ) as $name) {
	    if(!isset($data[$hostid][$sect][$name])) {
		$data[$hostid][$sect][$name] = 0;
	    }
	    if(!isset($data[$hostid][$year][$sect][$name])) {
		$data[$hostid][$year][$sect][$name] = 0;
	    }
	    if(!isset($data[$hostid][$year][$month][$sect][$name])) {
		$data[$hostid][$year][$month][$sect][$name] = 0;
	    }
	    if(!isset($data[$hostid][$year][$month][$day][$sect][$name])) {
		$data[$hostid][$year][$month][$day][$sect][$name] = 0;
	    }
	    if(!isset($data[$hostid][$year][$month][$day][$hour][$sect][$name])) {
		$data[$hostid][$year][$month][$day][$hour][$sect][$name] = 0;
	    }
	
	    // 
	    // Count reject and accepted (total, yearly, monthly, daily and by hour):
	    // 
	    $data[$hostid][$sect][$name] += $dragonx[$name];
	    $data[$hostid][$year][$sect][$name] += $dragonx[$name];
	    $data[$hostid][$year][$month][$sect][$name] += $dragonx[$name];
	    $data[$hostid][$year][$month][$day][$sect][$name] += $dragonx[$name];
	    $data[$hostid][$year][$month][$day][$hour][$sect][$name] += $dragonx[$name];
	}
    }
}

// 
// This function gets called to generate graphics from the data collected
// in call to collect_data_hook(). The graphdir argument is the directory
// where the image should be saved. The datetime is an timestamp suitable for
// printing dates on the image. The type is either "total", "yearly", "monthly",
// or "daily" and tells the function what kind of statistics the date argument
// contains.
// 
function graph_data_hook($graphdir, $hostid, $options, $datetime, $data, $type)
{
    $found  = false;
    $image  = sprintf("%s/dragonx.png", $graphdir);
    $labels = array();
    $values = array( "accepted" => array(),
		     "rejected" => array());
    $colors = array( "rejected"  => array( "start"    => "orange",
					   "end"      => "yellow",
					   "outline"  => "black" ),
		     "accepted"  => array( "start"    => "darkgreen",
					   "end"      => "green",
					   "outline"  => "black" ),
		     "text"      => array( "positive" => "black",
					   "negative" => "lightgray" ));

    // 
    // Skip unset data:
    // 
    if(!isset($data['dragonx'])) {
	return;
    }

    // 
    // Collect data for plot in the values array:
    // 
    switch($type) {
	// 
	// Generate total predicted molecules graph.
	// 
     case "total":
	$title = "Total Predicted Molecules";
	
	foreach($data as $year => $data1) {
	    if(is_numeric($year)) {
		foreach($data1 as $sect => $value) {
		    if($sect == "dragonx") {
			array_push($values['accepted'], $value['accepted']);
			array_push($values['rejected'], $value['rejected']);
			array_push($labels, $year);
			$found = true;
		    }
		}
	    }
	}
	if(count($labels) < 3) {
	    foreach(array_keys($values) as $key) {
		array_unshift($values[$key], 0);
		array_push($values[$key], 0);
	    }
	    array_unshift($labels, min($labels) - 1);
	    array_push($labels, max($labels) + 1);
	}
	array_multisort($labels, $values['accepted'], $values['rejected']);
	break;
	
	//
	// Generate by year predicted molecules graph.
	// 
     case "yearly":
	$title  = sprintf("Predicted Molecules %s", strftime("%G", $datetime));
	
	for($i = 0; $i < 12; ++$i) {
	    foreach(array_keys($values) as $key) {
		$values[$key][$i] = 0;
	    }
	    $labels[$i] = strftime("%b", mktime(0, 0, 0, $i + 1, 1, 1));
	}
    
	foreach($data as $month => $data1) {
	    if(is_numeric($month)) {
		foreach($data1 as $sect => $value) {
		    if($sect == "dragonx") {
			$index = intval($month) - 1;
			foreach(array_keys($values) as $key) {
			    $values[$key][$index] = $value[$key];
			}
			$found = true;
		    }
		}
	    }
	}
	break;
	
	// 
	// Generate by month predicted molecules graph.
	// 
     case "monthly":
	$title  = sprintf("Predicted Molecules %s", strftime("%B %G", $datetime));
	
	for($i = 0; $i < date('t', $datetime); ++$i) {
	    foreach(array_keys($values) as $key) {
		$values[$key][$i] = 0;
	    }
	    $labels[$i] = $i + 1;
	}
	
	foreach($data as $day => $data1) {
	    if(is_numeric($day)) {
		foreach($data1 as $sect => $value) {
		    if($sect == "dragonx") {
			$index = intval($day) - 1;
			foreach(array_keys($values) as $key) {
			    $values[$key][$index] = $value[$key];
			}
			$found = true;
		    }
		}
	    }
	}
	break;
	
	// 
	// Generate on daily basis predicted molecules graph.
	// 
     case "daily":
	$title  = sprintf("Predicted Molecules %s", strftime("%G-%m-%d", $datetime));
	
	for($i = 0; $i < 24; ++$i) {
	    foreach(array_keys($values) as $key) {
		$values[$key][$i] = 0;
	    }
	    $labels[$i] = sprintf("%02d", $i);
	}

	foreach($data as $hour => $data1) {
	    if(is_numeric($hour)) {
		foreach($data1 as $sect => $value) {
		    if($sect == "dragonx") {
			foreach(array_keys($values) as $key) {
			    $values[$key][intval($hour)] = $value[$key];
			}
			$found = true;
		    }
		}
	    }
	}
	break;
    }

    // 
    // Skip plotting empty graphs:
    // 
    if(!$found) {
	return;
    }
            
    // 
    // Plot an accumulated bar plot like example 23 in JpGraph's test suite:
    // 
    $width = 460;
    $height = 200;

    if(count($labels) > 5 && count($labels) <= 12 ) {
	$width += count($labels) * 4;
    }
    if(count($labels) > 12) {
	$width += count($labels) * 8;
    }
    
    // 
    // Create the graph and setup the basic parameters 
    // 
    $graph = new Graph($width, $height, "auto");    
    $graph->img->SetMargin(40, 130, 20, 40);
    $graph->SetScale("textint");
    $graph->SetFrame(true, JPGRAPH_FRAME_FOREGROUND_COLOR, JPGRAPH_FRAME_BORDER_WIDTH); 
    $graph->SetColor(JPGRAPH_GRAPH_BACKGROUND_COLOR);
    $graph->SetMarginColor(JPGRAPH_FRAME_BACKGROUND_COLOR);
    $graph->legend->SetFillColor(JPGRAPH_LEGEND_BACKGROUND_COLOR);
    $graph->legend->SetColor(JPGRAPH_LEGEND_FOREGROUND_COLOR, JPGRAPH_LEGEND_BORDER_COLOR);
    $graph->footer->right->Set(sprintf("Generated: %s", strftime("%G-%m-%d")));
    $graph->footer->right->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);

    // 
    // Add some grace to the top so that the scale doesn't
    // end exactly at the max value. 
    // 
    $graph->yaxis->scale->SetGrace(15);

    // 
    // Setup X-axis labels:
    // 
    $graph->xaxis->SetTickLabels($labels);
    $graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
    $graph->xaxis->SetColor(JPGRAPH_XAXIS_SCALE_COLOR, JPGRAPH_XAXIS_LABEL_COLOR);

    // 
    // Setup Y-axis labels:
    // 
    $graph->yaxis->SetColor(JPGRAPH_YAXIS_SCALE_COLOR, JPGRAPH_YAXIS_LABEL_COLOR);
    $graph->ygrid->SetColor(JPGRAPH_GRAPH_FOREGROUND_COLOR);
    $graph->yaxis->title->Set("Number");

    // 
    // Setup graph title ands fonts
    // 
    $graph->title->Set($title);
    $graph->title->SetColor(JPGRAPH_TITLE_MAIN_COLOR);
    $graph->title->SetFont(FF_FONT2, FS_BOLD);
    $graph->subtitle->Set(sprintf("(Total %d accepted and %d rejected)", 
				  array_sum($values['accepted']), 
				  array_sum($values['rejected'])));
    $graph->subtitle->SetColor(JPGRAPH_TITLE_SUBTITLE_COLOR);

    $graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL);
    $graph->xaxis->title->SetColor(JPGRAPH_NOTES_FOREGROUND_COLOR);
    
    // 
    // Create the bar plots:
    // 
    $accepted = new BarPlot($values['accepted']);
    $rejected = new BarPlot($values['rejected']);
    $accepted->Setlegend("Accepted");
    $rejected->Setlegend("Rejected");
    
    if(JPGRAPH_ENABLE_GRADIENTS) {
	$accepted->SetFillGradient($colors['accepted']['start'], $colors['accepted']['end'], GRAD_MIDVER);
	$rejected->SetFillGradient($colors['rejected']['start'], $colors['rejected']['end'], GRAD_MIDVER);
	$accepted->SetColor("black");
	$rejected->SetColor($colors['rejected']['outline']);
    }
    else {
	$accepted->SetFillColor($colors['accepted']['start']);
	$rejected->SetFillColor($colors['rejected']['start']);
    }
        
    $abplot = new AccBarPlot(array($accepted, $rejected));    
    $abplot->value->SetColor($colors['text']['positive'], $colors['text']['negative']);
    $abplot->value->SetAngle(90);
    $abplot->value->show();
    $abplot->value->SetFormat('%d');
    $abplot->SetWidth(0.5);

    $graph->Add($abplot);
    
    if($options->debug) {
	printf("debug: creating graphic file %s\n", $image);	
    }
    $graph->Stroke($image);
}

// 
// This function gets called from statistics.php to display the data collected
// by collect_data_hook() and to print the graphics generated by graph_data_hook().
// The $subsect argument is either "root", "year", "month" or "day" and is a
// hint.
// 
function show_data_hook($data, $statdir, $subsect)
{
    printf("<span id=\"secthead\">Predicted Molecules:</span>\n");
    
    printf("<p>The number of processed molecules for %s was %d totally.<br>%d where accepted (predicted) and %d where rejected (malformed)</p>\n", 
	   subsect_date_string($subsect),
	   $data['dragonx']['accepted'] + $data['dragonx']['rejected'], 
	   $data['dragonx']['accepted'], $data['dragonx']['rejected']); 
    
    printf("<p>\n");
    if(file_exists(sprintf("%s/dragonx.png", $statdir))) {
	printf("<img src=\"image.php?%s\">\n", 
	       request_params(array( "image" => "dragonx" )));
    }
    printf("</p>\n");
}

?>
