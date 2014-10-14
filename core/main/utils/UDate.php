<?php
/**
 * Universal Date object
 * 
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
class UDate
{
	/**
	 * @var DateTime
	 */
	private $_dateTime;
	/**
	 * constructor
	 * 
	 * @param string $string   The date time string: 2010-01-01 00:00:00 | now
	 * @param string $timeZone The timezone string: "Australia/Melbourne"
	 */
	public function __construct($string = "now", $timeZone = "UTC")
	{
		if ($string == "0000-00-00 00:00:00")
			$string = trim(UDate::zeroDate());
		// Is there a difference between UTC and GMT?
		if($timeZone === '')
			$this->_dateTime = date_create($string);
		else
			$this->_dateTime = date_create($string, new DateTimeZone($timeZone));
	}
	/**
	 * getting a zero date object
	 * 
	 * @return UDate
	 */
	public static function zeroDate()
	{
	    $date = new UDate();
	    $date->setDate(1, 1, 1);
	    $date->setTime(0, 0, 0);
	    return $date;
	}
	/**
	 * getting a max date object
	 * 
	 * @return UDate
	 */
	public static function maxDate()
	{
	    $date = new UDate();
	    $date->setDate(31, 12, 9999);
	    $date->setTime(23, 59, 59);
	    return $date;
	}
	/**
	 * getting now datetime
	 * 
	 * @return UDate
	 */
	public static function now($timeZone = 'UTC')
	{
	    return new UDate('now', $timeZone);
	}
	/**
	 * Magic toString function
	 * @return string
	 */
	public function __toString()
	{
		try
		{
			$dt = $this->getDateTimeString("Y-m-d H:i:s");
			if ($dt === "0000-00-00 00:00:00")
				$dt = (string)UDate::zeroDate();
		}
		catch (Exception $ex)
		{
			$dt = (string)UDate::zeroDate();
		}	
		return $dt;
	}
	/**
	 * setting TimeZone of DateTime Object
	 * 
	 * @param string $timeZone The timezone string: "UTC"
	 * 
	 * @return UDate
	 */
	public function setTimeZone($timeZone = 'UTC')
	{
	    $this->_dateTime->setTimezone(new DateTimeZone($timeZone));
	    return $this;
	}
    /**
     * getting TimeZone of DateTime Object
     * 
     * @return DateTimeZone
     */
	public function getTimeZone()
	{
		return $this->_dateTime->getTimezone();
	}
	/**
	 * Returns the internal DateTime object
	 *
	 * @return DateTime
	 */
	public function getDateTime()
	{
		return $this->_dateTime;
	}
	/**
	 * Getting the difference between two dates
	 * 
	 * @param UDate $date The other datetime to compare with
	 * 
	 * @return DateInterval
	 */
	public function diff(UDate $date)
	{
	    return $this->getDateTime()->diff($date->getDateTime());
	}
	/**
	 * Returns only the date part of the internal DateTime object
	 *
	 * @param string $format The date format for DateTime
	 * 
	 * @return string
	 */
	public function getDateTimeString($format = "Y-m-d")
	{
		return date_format($this->_dateTime, $format);
	}
	/**
	 * Test if the date is before the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */
	public function before(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() < $dateTime->getUnixTimeStamp();
	}
	/**
	 * Test if the date is after the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */
	public function after(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() > $dateTime->getUnixTimeStamp();
	}
	/**
	 * Test if the date is before or equal to the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */	
	public function beforeOrEqualTo(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() <= $dateTime->getUnixTimeStamp();
	}	
	/**
	 * Test if the date is after or equal to the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */	
	public function afterOrEqualTo(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() >= $dateTime->getUnixTimeStamp();
	}
	/**
	 * Test if the date is equal to the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */
	public function equal(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() === $dateTime->getUnixTimeStamp();
	}
	/**
	 * Test if the date is not equal to the date passed in
	 *
	 * @param UDate $dateTime The UDate object that we are compare with
	 * 
	 * @return bool
	 */
	public function notEqual(UDate $dateTime)
	{
		return $this->getUnixTimeStamp() !== $dateTime->getUnixTimeStamp();
	}
	/**
	 * Wraps the PHP date_modify function
	 *
	 * @param string $string The modify string see{@link http://php.net/manual/en/datetime.formats.php}
	 * 
	 * @return UDate
	 */
	public function modify($string)
	{
	    $this->_dateTime->modify($string);
		return $this;
	}
	/**
	 * Set the date based on the inputs
	 *
	 * @param int $day   The int day number
	 * @param int $month The int month number
	 * @param int $year  The int year number
	 * 
	 * @return UDate
	 */
	public function setDate($day, $month, $year)
	{
	    $this->_dateTime->setDate($year, $month, $day);
		return $this;
	}
	/**
	 * Set the time based on the inputs
	 *
	 * @param int $hour   The int hour number
	 * @param int $minute The int minute number
	 * @param int $second The int second number
	 * 
	 * @return UDate
	 */
	public function setTime($hour, $minute, $second)
	{
	    $this->_dateTime->setTime($hour, $minute, $second);
	    return $this;
	}
	/**
	 * Returns Unix TimeStamp
	 *
	 * @return int unixtimestamp
	 */
	public function getUnixTimeStamp()
	{
		return $this->_dateTime->format('U');
	}
	/**
	 * formating the datetime object
	 * 
	 * @param string $format The format
	 * 
	 * @return string
	 */
	public function format($format)
	{
	    return $this->_dateTime->format($format);
	}
}

?>