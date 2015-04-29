<?php

class plgDPCalendarDPCalendar_HiorgInstallerScript
{

 public function install($parent)
 {
  // Enable plugin

  $db  = JFactory::getDbo();
  $query = $db->getQuery(true);
  $query->update('#__extensions');
  $query->set($db->quoteName('enabled') . ' = 1');
  $query->where($db->quoteName('element') . ' = ' . $db->quote('dpcalendar_hiorg'));
  $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
  $db->setQuery($query);
  $out = $db->execute();
 }

}