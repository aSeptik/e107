<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * Chatbox e_search addon 
 */
 

if (!defined('e107_INIT')) { exit; }

// v2 e_search addon. 
// Removes the need for search_parser.php, search_advanced.php and in most cases search language files. 

class download_search extends e_search // include plugin-folder in the name.
{
		
	function config()
	{
		$sql = e107::getDb();
					
		$catList = array();
		
		$catList[] = array('id' => 'all', 'title' => LAN_SEARCH_51);
		
		if ($sql ->gen("SELECT download_category_id, download_category_name FROM #download_category WHERE download_category_parent != 0 AND download_category_class IN (".USERCLASS_LIST.")")) 
		{
			while($row = $sql->fetch()) 
			{
				$catList[] = array('id' => $row['download_category_id'], 'title' => $row['download_category_name']);
			//	$advanced_caption['title'][$row['category_id']] = 'News -> '.$row['category_name'];
			}
		}			
				
				
		$search = array(
			'name'			=> LAN_PLUGIN_DOWNLOAD_NAME,
			'table'			=> 'download AS d LEFT JOIN #download_category AS c ON d.download_category = c.download_category_id',

			'advanced' 		=> array(
								'cat'	=> array('type'	=> 'dropdown', 		'text' => LAN_SEARCH_55, 'list'=>$catList),
								'date'	=> array('type'	=> 'date', 			'text' => LAN_SEARCH_50),
								'author'=> array('type'	=> 'author',		'text' => LAN_SEARCH_61)
							),
							
			'return_fields'	=> array('d.download_id', 'd.download_category', 'download_category_id', 'd.download_name', 'd.download_description', 'd.download_author', 'd.download_author_website', 'd.download_datestamp', 'd.download_class', 'c.download_category_name', 'c.download_category_class'), 
			'search_fields'	=> array('d.download_name'=> '1.2', 'd.download_url' => '0.9', 'd.download_description'=>'0.6', 'd.download_author'=>'0.6', 'd.download_author_website'=>'0.4'), // fields and weights. 
			
			'order'			=> array('download_datestamp' => DESC),
			'refpage'		=> e_PLUGIN_ABS.'download/download.php'
		);


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();

		//TODO SEF URLs for list below. 
		//TODO Remove html from pre_summary and use additional vars instead. 

		$res = array();
	
		$datestamp = $tp -> toDate($row['download_datestamp'], "long");
		
		$res['link'] 		= e107::getUrl()->create('download/view/item', array('id'=>$row['download_id'],'name'=>vartrue($row['download_sef'],'--no-sef-set--')));
		$res['pre_title'] 	= $tp->toHtml($row['download_category_name'],false,'TITLE_PLAIN')." | ";
		$res['title'] 		= $row['download_name'];
		$res['pre_summary'] = "<div class='smalltext'><a href='download.php'>".LAN_197."</a> -> <a href='download.php?list.".$row['download_category_id']."'>".$row['download_category_name']."</a></div>";
		$res['summary'] 	= $row['download_description'];
		$res['detail'] 		= LAN_SEARCH_15." ".$row['download_author']." | ".LAN_SEARCH_66.": ".$datestamp;



		return $res;
		
	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm='')
	{
		$tp = e107::getParser();
		
		$qry = "download_active > '0' AND d.download_visible IN (".USERCLASS_LIST.") AND c.download_category_class IN (".USERCLASS_LIST.") AND".$advanced_where;

		if (isset($parm['cat']) && is_numeric($parm['cat'])) 
		{
			$qry .= " d.download_category='".$parm['cat']."' AND";
		}
		
		if (isset($parm['time']) && is_numeric($parm['time'])) 
		{
			$qry .= " d.download_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}
		
		if (isset($parm['author']) && $parm['author'] != '') 
		{
			$qry .= " (d.download_author = '".$tp -> toDB($parm['author'])."') AND";
		}
		
		return $qry;
	}
	

}


?>