<?php
/**
 * @package    DPCalendar - HiOrg-Server
 * @author     HiOrg Server GmbH https://www.hiorg-server.de
 * @copyright  Copyright (c) HiOrg Server GmbH / Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR))
{
	return;
}

class PlgDPCalendarHiorg extends \DPCalendar\Plugin\SyncPlugin
{
	/**
	 * Plugin identifier
	 *
	 * @var  string
	 */
	protected $identifier = 'hiorg';

	/**
	 * Retrun the ICal URL
	 *
	 * @param  \stdClass  $calendar
	 *
	 * @return  string
	 */
	protected function getIcalUrl($calendar)
	{
		return 'https://www.hiorg-server.de/termine.php?ical=1&ov='. $calendar->params->get('uri');
	}

	/**
	 * Getting the sync token to determine if a full sync needs to be done.
	 *
	 * @param  \stdClass  $calendar
	 *
	 * @return  boolean
	 */
	protected function getSyncToken($calendar)
	{
		$uri = $this->getIcalUrl($calendar);

		if (!$uri)
		{
			return rand();
		}

		$internal = !filter_var($uri, FILTER_VALIDATE_URL);

		if ($internal && strpos($uri, '/') !== 0)
		{
			$uri = JPATH_ROOT . DS . $uri;
		}

		$syncToken = rand();

		if ($internal)
		{
			$timestamp = filemtime($uri);

			if ($timestamp)
			{
				$syncToken = $timestamp;
			}
		}
		else 
		{
			$http     = \JHttpFactory::getHttp();
			$response = $http->head($uri);

			if (key_exists('ETag', $response->headers))
			{
				$syncToken = $response->headers['ETag'];
			}
			elseif (key_exists('Last-Modified', $response->headers))
			{
				$syncToken = $response->headers['Last-Modified'];
			}
		}

		return $syncToken;
	}

	/**
	 * Return the cleaned up content of the calendar
	 *
	 * @param integer    $calendarId  The calender Id
	 * @param JDate      $startDate   The start date
	 * @param JDate      $endDate     The end date
	 * @param JRegistry  $options     The options passed by the plugin
	 *
	 * @return  string  The content of the calendar
	 */
	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);

		if (empty($calendar))
		{
			return '';
		}

		if (method_exists('DPCalendarHelper', 'fetchContent')) {
            $content = \DPCalendarHelper::fetchContent($this->getIcalUrl($calendar));
        } else {
            $content = $this->fetchContent($this->getIcalUrl($calendar));
        }

		if ($content instanceof \Exception)
		{
			$this->log($content->getMessage());

			return '';
		}

		$content = str_replace("BEGIN:VCALENDAR\r\n", '', $content);
		$content = str_replace("BEGIN:VCALENDAR\n", '', $content);
		$content = str_replace("\r\nEND:VCALENDAR", '', $content);
		$content = str_replace("\nEND:VCALENDAR", '', $content);

		return "BEGIN:VCALENDAR\n" . $content . "\nEND:VCALENDAR";
	}
}
