<?php
/**
 * @package		DPCalendarHiorg
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 Digital Peak, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.plugin', JPATH_ADMINISTRATOR);
if (! class_exists('DPCalendarPlugin')) {
    return;
}
class plgDPCalendarDPCalendar_Hiorg extends DPCalendarPlugin {
    
    
        protected $hiorg_url = "https://www.hiorg-server.de/termine.php?ical=1";
	protected $identifier = 'h';
        
        
        public function __construct(&$subject, $config = array()) {
            if ($this->isImport()) {
                JError::raiseError(500, "Das HiOrg-Plugin unterstuezt den Import nicht.");
                die("Importing is not supported and not needed in order for this plugin to operate correctly!. - Das Importieren wird nicht unterstuetzt und nicht benoetigt, damit dieses Plugin korrekt funktioniert.");
            }
            parent::__construct($subject, $config);
        }
        
	public function fetchCalendars() {	
            $calendars = array();
	    $title = $this->params->get('title-1', null);
            $calendars[] = $this->createCalendar(1, $title, $this->params->get('description-1', ''), $this->params->get('color-1', 'A32929'));
            
            //var_dump($calendars);
            return $calendars;
                
	}
        
        
        
       protected function getContent() {
        $uri = $this->hiorg_url."&ov=".$this->params->get('ov-1', null);
        $content = DPCalendarHelper::fetchContent(str_replace('webcal://', 'https://', $uri));

        $content = str_replace("BEGIN:VCALENDAR\r\n", '', $content);
        $content = str_replace("\r\nEND:VCALENDAR", '', $content);
        $this->str_replace_broken_html("\\n", "<br>", $content);
        //echo $uri;
        //var_dump($content);
        /*
        foreach ($arr as $key => $val) {
            echo $key;
            $this->str_replace_broken_html($val, $key, $content);
        }
        $this->str_replace_broken_html("\\n", "<br>", $content);
        */
        return "BEGIN:VCALENDAR\r\n" . $content . "\r\nEND:VCALENDAR";
    }

    
    private function isImport() {
        
        $arr = JRequest::getVar("calendar", null);
        $bool = false;
        if (is_array($arr)) {
        
        foreach ($arr as $val) {
            if (substr($val, 0,1) == "h") {
                $bool = true;
                break;
            }
        }
        }
		return JFactory::getApplication()->isAdmin() && JRequest::getVar('option', null) == 'com_dpcalendar' &&
		(JRequest::getVar('task', null) == 'add' && $bool /*|| (JRequest::getVar('view', null) == 'tools' && JRequest::getVar('layout', null) == 'import')*/);
	}
        
        /**
         * Findet Vorkommen von $needle in $haystack und ersetzt diese druch $repl, auch wenn $needle durch Zeilenumbrüche getrennt ist.
         * 
         * @param type $needle
         * @param type $repl
         * @param type $haystack
         */
        private function str_replace_broken_html($needle, $repl, &$haystack) {
    
        $haystack = str_replace($needle, $repl, $haystack);
        for ($index = 1; $index < strlen($needle); $index++) {
        $temp=substr($needle, 0, $index);
        $temp=$temp."\r\n  ";
        $temp2 = substr($needle, $index);
        $temp = $temp.$temp2;
        $haystack = str_replace($temp, $repl, $haystack);
   
         }
    
    
    
}

    
}