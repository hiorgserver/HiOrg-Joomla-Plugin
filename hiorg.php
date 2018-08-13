<?php
/**
 * @package    DPCalendar - HiOrg-Server
 * @author     HiOrg Server GmbH https://www.hiorg-server.de
 * @copyright  Copyright (c) HiOrg Server GmbH / Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarHiorg extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'hiorg';

	protected function getIcalUrl($calendar)
	{
		return "https://www.hiorg-server.de/termine.php?ical=1&ov=". $calendar->params->get('uri');
	}

	protected function getSyncToken($calendar)
	{
		$uri = $this->getIcalUrl($calendar);

		if (!$uri) {
			return rand();
		}

		$internal = !filter_var($uri, FILTER_VALIDATE_URL);
		if ($internal && strpos($uri, '/') !== 0) {
			$uri = JPATH_ROOT . DS . $uri;
		}

		$syncToken = rand();
		if ($internal) {
			$timestamp = filemtime($uri);
			if ($timestamp) {
				$syncToken = $timestamp;
			}
		} else {
			$http     = \JHttpFactory::getHttp();
			$response = $http->head($uri);

			if (key_exists('ETag', $response->headers)) {
				$syncToken = $response->headers['ETag'];
			} else if (key_exists('Last-Modified', $response->headers)) {
				$syncToken = $response->headers['Last-Modified'];
			}
		}

		return $syncToken;
	}


	protected function getContent($calendarId, JDate $startDate = null, JDate $endDate = null, JRegistry $options)
	{
		$calendar = $this->getDbCal($calendarId);
		if (empty($calendar)) {
			return '';
		}
		$content = \DPCalendarHelper::fetchContent($this->getIcalUrl($calendar));

		if ($content instanceof \Exception) {
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
