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
// Configures the JpGraph library used when generating graphics from 
// statistics data.
// 

// 
// Set to the path of your JpGraph installation if its not already in PHP's 
// include path. The path defined should contain the file jpgraph.php.
// 
define ("JPGRAPH_LIB_PATH", "/usr/share/php5/jpgraph");

// 
// Various color, font and size definitions common for all generated graphics.
// Colors can be defined by their generic name (like navyblue), see system 
// rgb.txt for example.
// 

// 
// The image frame.
// 
define ("JPGRAPH_FRAME_FOREGROUND_COLOR", "#666666");
define ("JPGRAPH_FRAME_BACKGROUND_COLOR", "#eeeeee");
define ("JPGRAPH_FRAME_BORDER_WIDTH", 1);

// 
// The image diagram.
// 
define ("JPGRAPH_GRAPH_FOREGROUND_COLOR", "#666666");
define ("JPGRAPH_GRAPH_BACKGROUND_COLOR", "#ffffff");
define ("JPGRAPH_GRAPH_LINE_WIDTH", 1);

// 
// The legend box.
// 
define ("JPGRAPH_LEGEND_BACKGROUND_COLOR", "#ffffff");
define ("JPGRAPH_LEGEND_FOREGROUND_COLOR", "#000000");
define ("JPGRAPH_LEGEND_BORDER_COLOR", "#000000");

// 
// The X-axis.
// 
define ("JPGRAPH_XAXIS_SCALE_COLOR", "#aaaaaa");
define ("JPGRAPH_XAXIS_LABEL_COLOR", "#333333");

// 
// The Y-axis.
// 
define ("JPGRAPH_YAXIS_SCALE_COLOR", "#aaaaaa");
define ("JPGRAPH_YAXIS_LABEL_COLOR", "#333333");

// 
// The image title.
// 
define ("JPGRAPH_TITLE_MAIN_COLOR", "#000000");
define ("JPGRAPH_TITLE_SUBTITLE_COLOR", "#333333");

// 
// The image notes (at bottom).
// 
define ("JPGRAPH_NOTES_FOREGROUND_COLOR", "#666666");

// 
// Enable antialiasing? This might improve the visual appearance of pie graphs
// at the expense of longer image generation time.
// 
define ("JPGRAPH_ENABLE_ANTIALIASING", false);

// 
// Enable gradient bars? The value is a bitmask of one or more of the 
// following constants:
// 
define ("JPGRAPH_GRADIENT_NONE", 0);
define ("JPGRAPH_GRADIENT_SUBMIT", 1);
define ("JPGRAPH_GRADIENT_PROCTIME", 2);
define ("JPGRAPH_GRADIENT_ALL", JPGRAPH_GRADIENT_SUBMIT | JPGRAPH_GRADIENT_PROCTIME);
define ("JPGRAPH_ENABLE_GRADIENTS", JPGRAPH_GRADIENT_ALL);

?>
