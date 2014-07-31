<?php
namespace Soter;
/*
 * From Wikipedia: "Soter [...] spirit of safety, preservation and
 * deliverance from harm
 *
 * @author Dayan Paez
 * @created 2011-05-17
 */

/**
 * The great validator
 *
 * @author Dayan Paez
 * @create 2011-05-17
 * @package validation
 */
class Soter {

  private $dbm = null;

  /**
   * @var Array map of SoterException fields => message format messages
   */
  protected $MESSAGES = array(
    SoterException::INT_MISS => 'not found',
    SoterException::INT_NONE => 'non-numeric',
    SoterException::INT_LESS => 'less than %d',
    SoterException::INT_MORE => 'more than %d',

    SoterException::FLT_MISS => 'not found',
    SoterException::FLT_NONE => 'non-numeric',
    SoterException::FLT_LESS => 'less than %0.1f',
    SoterException::FLT_MORE => 'more than %0.1f',

    SoterException::KEY_MISS => 'not found',
    SoterException::KEY_NONE => 'unexpected value',

    SoterException::VAL_MISS => 'not found',
    SoterException::VAL_NONE => 'unexpected value',

    SoterException::STR_MISS => 'not found',
    SoterException::STR_NONE => 'not a string',
    SoterException::STR_LESS => 'less than %d characters long',
    SoterException::STR_MORE => 'longer than %d characters',
    SoterException::STR_CODE => 'invalid encoding',

    SoterException::DBO_MISS => 'not found',
    SoterException::DBO_NONE => 'invalid ID',

    SoterException::LST_MISS => 'missing list',
    SoterException::LST_NONE => 'not a list',
    SoterException::LST_SIZE => 'invalid size, expected %d',

    SoterException::DATE_MISS => 'not found',
    SoterException::DATE_NONE => 'not a date',
    SoterException::DATE_LESS => 'earlier than %s',
    SoterException::DATE_MORE => 'later than %s',

    SoterException::FILE_MISS => 'not found',
    SoterException::FILE_NONE => 'none submitted',
    SoterException::FILE_NOERROR => 'unknown error',
    SoterException::FILE_LESS => 'smaller than %d bytes',
    SoterException::FILE_MORE => 'larger than %d bytes',
    SoterException::FILE_ERROR => 'server upload error %d',

    SoterException::RE_MISS => 'not found',
    SoterException::RE_MATCH => 'does not match pattern',

    SoterException::EMAIL_MISS => 'not found',
    SoterException::EMAIL_NONE => 'invalid email',

    SoterException::URL_MISS => 'not found',
    SoterException::URL_NONE => 'invalid URL',

    JSON_ERROR_DEPTH => 'maximum stack depth exceeded',
    JSON_ERROR_STATE_MISMATCH => 'invalid or malformed JSON',
    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
    JSON_ERROR_SYNTAX => 'Syntax error',
    JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',

    SoterException::DNS_MISS => 'not found',
    SoterException::DNS_NONE => 'invalid domain name',
    SoterException::DNS_SIZE => 'invalid domain name size',
    SoterException::DNS_INVALID_SUB => 'invalid subdomain',
    SoterException::DNS_CHARACTERS => 'invalid characters not allowed',
  );

  /**
   * Creates a new validator
   *
   */
  public function __construct() {}

  /**
   * Helper function throws consistent SoterExceptions
   *
   * @param String $mes the sprintf of the error message (from client)
   * @param SoterException::Const the error
   * @param String $arg the optional pertinent argument
   * @throws SoterException unconditionally
   */
  final protected function panic($mes, $const, $arg = "") {
    throw new SoterException(sprintf($mes, sprintf($this->MESSAGES[$const], $arg), $const));
  }

  /**
   * Set the DBM (sub)class to use for validating against a
   * database. Because of the static nature of the DBM class, this is
   * merely the name of the class to use when serializing.
   *
   * @param DBI $dbi the database class to use
   * @see reqDBM
   * @see incDBM
   * @see hasDBM
   * @throws InvalidArgumentException if no such class exists
   */
  final public function setDBI($dbi) {
    if (!class_exists('\\MyORM\\DBI') || !($dbi instanceof \MyORM\DBI))
      throw new \InvalidArgumentException("DBI class does not exist: requires MyORM\DBI");
    $this->dbm = $dbi;
  }

  /**
   * Requires that an integer be present as key $key in array $arg
   *
   * @param Array $args the array to check for int, such as $_POST
   * @param String $key the key that should be present
   * @param int $min the minimum allowed value (inclusive)
   * @param int $max the maximum allowed value (exclusive)
   * @param String $mes the error message to throw upon failure
   * @return int the (truncated) value
   * @throws SoterException
   */
  final public function reqInt(Array $args, $key, $min = 0, $max = PHP_INT_MAX, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::INT_MISS);
    if (!is_numeric($args[$key]))
      $this->panic($mes, SoterException::INT_NONE);
    $val = (int)$args[$key];
    if ($val < $min)
      $this->panic($mes, SoterException::INT_LESS, $min);
    if ($val >= $max)
      $this->panic($mes, SoterException::INT_MORE, $max);
    return $val;
  }

  /**
   * Requires that a float be present as key $key in array $arg
   *
   * @param Array $args the array to check for float, such as $_POST
   * @param String $key the key that should be present
   * @param int $min the minimum allowed value (inclusive)
   * @param int $max the maximum allowed value (inclusive)
   * @param String $mes the error message to throw upon failure
   * @return float the value
   * @throws SoterException
   */
  final public function reqFloat(Array $args, $key, $min = 0, $max = PHP_INT_MAX, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::FLT_MISS);
    if (!is_numeric($args[$key]))
      $this->panic($mes, SoterException::FLT_NONE);
    $val = $args[$key];
    if ($val < $min)
      $this->panic($mes, SoterException::FLT_LESS, $min);
    if ($val > $max)
      $this->panic($mes, SoterException::FLT_MORE, $max);
    return $val;
  }

  /**
   * Requires that the given $key be contained in the keys to the
   * array given, i.e. in_array($args[$key], array_keys($values))
   *
   * @see reqValue
   * @param Array $args the array to check
   * @param String $key the key
   * @param Array $values the associative array of values
   * @param String $mes the error message to throw
   * @return String $key the key
   * @throws SoterException
   */
  final public function reqKey(Array $args, $key, Array $values, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::KEY_MISS);
    if (!isset($values[$args[$key]]))
      $this->panic($mes, SoterException::KEY_NONE);
    return $args[$key];
  }

  /**
   * Requires that the value in $args[$key] be in the array $values
   *
   * @see reqKey
   * @param Array $args the array to check
   * @param String $key the key
   * @param Array $values the associative array of values
   * @param String $mes the error message to throw
   * @return String $key the key
   * @throws SoterException
   */
  final public function reqValue(Array $args, $key, Array $values, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::VAL_MISS);
    if (!in_array($args[$key], $values))
      $this->panic($mes, SoterException::VAL_NONE);
    return $args[$key];
  }

  /**
   * Requires a string be present in $args[$key], with a minimum
   * length and a maximum length as given (after trimming)
   *
   * @param Array $args the array
   * @param String $key the key in the array that contains string
   * @param int $min the minimum size (inclusive)
   * @param int $max the maximum size (exclusive)
   * @param String $mes the error message to throw
   * @return String the value
   * @throws SoterException
   */
  final public function reqString(Array $args, $key, $min = 0, $max = 8388608, $mes = "GSE") {
    return self::reqRaw($args, $key, $min, $max, $mes, array('trim'));
  }

  /**
   * Sometimes, we do not want to trim the string input, such as when
   * it is being fed to the DPEditor. For such an occassion, use this
   * method. It is actually a generic form of the reqString above.
   *
   * @param Array $args the array
   * @param String $key the key in the array that contains string
   * @param int $min the minimum size (inclusive)
   * @param int $max the maximum size (exclusive)
   * @param String $mes the error message to throw
   *
   * @param Array:callback $opers an array of callbacks to apply to
   * the string. These callbacks should take one parameter (a string)
   * and return the modified string.
   *
   * @return String the value
   * @throws SoterException
   */
  final public function reqRaw(Array $args, $key, $min = 0, $max = 8388608, $mes = "GSE", $opers = array()) {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::STR_MISS);
    if (!is_string($args[$key]))
      $this->panic($mes, SoterException::STR_NONE);
    $val = $args[$key];
    foreach ($opers as $oper)
      $val = call_user_func($oper, $val);
    $len = mb_strlen($val, 'UTF-8');
    if ($len < $min)
      $this->panic($mes, SoterException::STR_LESS, $min);
    if ($len >= $max)
      $this->panic($mes, SoterException::STR_MORE, $max);
    if (!mb_check_encoding($val, 'UTF-8'))
      $this->panic($mes, SoterException::STR_CODE);
    return $val;
  }

  /**
   * Requires that the value $args[$key] exist and be an ID of an
   * object of type $obj, which will be serialized by calling
   * DBM::get.  The DBM class to use is the one set with the setDBM
   * method.
   *
   * @see setDBM
   * @param Array $args the list to find the object ID
   * @param String $key the key in the $args where the ID is
   * @param DBObject $obj the type of object to serialize
   * @param String $mes the error message to provide
   * @return DBObject an object of the same type as passed
   * @throws SoterException
   */
  final public function reqDBObject(Array $args, $key, $obj, $mes = "GSE") {
    if ($this->dbm === null)
      throw new \RuntimeException("No database connection associated.");
    if (!($obj instanceof \MyORM\DBObject))
      throw new \RuntimeException("Expected MyORM\\DBObject argument.");
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::DBO_MISS);
    $obj = call_user_func(array($this->dbm, 'get'), $obj, $args[$key]);
    if ($obj === null)
      $this->panic($mes, SoterException::DBO_NONE);
    return $obj;
  }

  /**
   * Requires that $args[$key] exist and that it be an array, with an
   * optional size requirement
   *
   * @param Array $args the list where to find the required list
   * @param String $key the key in $args where to find the list
   * @param int|null $size if greater than 0, the exact size
   * @param String $mes the error message to return
   * @return Array the resulting list
   * @throws SoterException
   */
  final public function reqList(Array $args, $key, $size = null, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::LST_MISS);
    if (!is_array($args[$key]))
      $this->panic($mes, SoterException::LST_NONE);
    if ($size > 0 && count($args[$key]) != $size)
      $this->panic($mes, SoterException::LST_SIZE, $size);
    return $args[$key];
  }

  /**
   * Requires that each of the keys in $keys be keys in $args, each of
   * which points to a list. All the lists must be of the same size.
   *
   * @param Array $args the list, like $_POST
   * @param Array $keys the list of keys to be present in $args
   * @param int|null $size the exact size requirement, if positive
   * @param String $mes the error message to spit out
   * @return Array:Array the map
   * @throws SoterException
   * @see reqList
   */
  final public function reqMap(Array $args, Array $keys, $size = null, $mes = "GSE") {
    $map = array();
    foreach ($keys as $key) {
      $map[$key] = $this->reqList($args, $key, $size, $mes);
      if ($size === null)
	$size = count($map[$key]);
    }
    return $map;
  }

  /**
   * Requires that $args[$key] exist and be a valid date, optionally
   * between a $min and $max value. If the date is not properly
   * formatted under $args[$key], a SoterException is thrown.
   *
   * @param Array $args where to look for the date
   * @param String $key the key in $args where the date is hiding
   * @param DateTime|null $min the minimum value (inclusive)
   * @param DateTime|null $max the maximum value (exclusive)
   * @param String $mes the error message to throw
   * @throws SoterException
   */
  final public function reqDate(Array $args, $key, \DateTime $min = null, \DateTime $max = null, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::DATE_MISS);
    try {
      $date = new \DateTime($args[$key]);
    }
    catch (Exception $e) {
      $this->panic($mes, SoterException::DATE_NONE);
    }
    if ($min !== null && $date < $min)
      $this->panic($mes, SoterException::DATE_LESS, $min->format('Y/m/d H:i:s'));
    if ($max !== null && $date >= $max)
      $this->panic($mes, SoterException::DATE_MORE, $max->format('Y/m/d H:i:s'));
    return $date;
  }

  /**
   * Requires that $args[$key] be an array regarding an uploaded file,
   * such as one would find from $_FILES.
   *
   * @param Array $args the list of uploaded files: $_FILES would work
   * @param String $key the specific file one is searching
   * @param int $min the minimum size in bytes
   * @param int $max the maximum size in bytes
   * @param String $mes the error message
   * @return Array list with the necessary keys: 'tmp_name', etc.
   * @throw SoterException
   */
  final public function reqFile(Array $args, $key, $min = 0, $max = 8388608, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::FILE_MISS);
    if (!is_array($args[$key]))
      $this->panic($mes, SoterException::FILE_NONE);
    if (!isset($args[$key]['error']))
      $this->panic($mes, SoterException::FILE_NOERROR);
    if (!isset($args[$key]['size']) || $args[$key]['size'] < $min)
      $this->panic($mes, SoterException::FILE_LESS, $min);
    if ($args[$key]['size'] >= $max)
      $this->panic($mes, SoterException::FILE_MORE, $max);
    if ($args[$key]['error'] != 0)
      $this->panic($mes, SoterException::FILE_ERROR, $args[$key]['error']);
    return $args[$key];
  }

  /**
   * Useful for an array of files, as found in $_FILES.
   *
   * Similar to reqFile, but will return a list of associative arrays,
   * each with indices 'name', 'type', 'tmp_name', 'error', 'size'.
   *
   * This is notably different from the way $_FILES is normally
   * populated
   *
   * @see reqFiles
   * @return Array:Map may be empty
   */
  final public function reqFiles(Array $args, $key, $min = 0, $max = 8388608, $mes = "GSE") {
    $files = array();
    $templ = array('name' => null, 'tmp_name' => null, 'type' => null, 'error' => null, 'size' => null);
    foreach ($this->reqList($args, $key, 5, $mes) as $axis => $sublist) {
      if (!array_key_exists($axis, $templ))
        $this->panic($mes, SoterException::FILE_MISS);
      if (!is_array($sublist))
        $this->panic($mes, SoterException::FILE_MISS);
      for ($i = 0; $i < count($sublist); $i++) {
        if (count($files) <= $i)
          $files[$i] = $templ;
        $files[$i][$axis] = $sublist[$i];
      }
    }

    // Verify each file
    foreach ($files as $i => $file)
      $this->reqFile($files, $i, $min, $max, $mes);
    return $files;
  }

  /**
   * Requires that $args[$key] exists and satisfies the given regular
   * expression, which must work. If the regular expression does not
   * work, (preg_match returns false), a RuntimeException is thrown.
   *
   * @param Array $args the list in which to check for $key
   * @param String $key the key
   * @param String $regex the (well-formed) regular expression
   * @param String $mes the message of the exception upon failure
   * @return Array the matches of the regular expression. IE the
   * array filled in by PHP's preg_match
   * @throws SoterException
   */
  final public function reqRE(Array $args, $key, $regex, $mes = "GSE") {
    if (!isset($args[$key]))
      $this->panic($mes, SoterException::RE_MISS);
    $matches = array();
    $res = preg_match($regex, $args[$key], $matches);
    if ($res === false)
      throw new \RuntimeException("Invalid regex provided $regex.");
    if ($res == 0)
      $this->panic($mes, SoterException::RE_MATCH);
    return $matches;
  }

  /**
   * Requires that $args[$key] exist and be a real e-mail address.
   *
   * This function uses PHP's internal filter_var method for its
   * magic.
   *
   * @param Array $args the list in which to check for $key
   * @param String $key the key
   * @param String $mes the exception message upon failure
   * @return String the email address
   * @throws SoterException
   */
  final public function reqEmail(Array $args, $key, $mes = "GSE") {
    if (!isset($args[$key]) || !is_string($args[$key]) || strlen($args[$key]) == 0)
      $this->panic($mes, SoterException::EMAIL_MISS);
    if (($email = filter_var($args[$key], FILTER_VALIDATE_EMAIL)) === false)
      $this->panic($mes, SoterException::EMAIL_NONE);
    return $email;
  }

  /**
   * Requires that $args[$key] exist and be a URL.
   *
   * This function uses PHP's internal filter_var method for its
   * magic, thus it is succeptible to the warnings therein.
   *
   * @param Array $args the list in which to check for $key
   * @param String $key the key
   * @param String $mes the exception message upon failure
   * @return String the URL
   * @throws SoterException
   */
  final public function reqURL(Array $args, $key, $mes = "GSE") {
    if (!isset($args[$key]) || !is_string($args[$key]) || strlen($args[$key]) == 0)
      $this->panic($mes, SoterException::URL_MISS);
    if (($url = filter_var($args[$key], FILTER_VALIDATE_URL)) === false)
      $this->panic($mes, SoterException::URL_NONE);
    return $url;
  }

  /**
   * Requires that $args[$key] exist and be a valid JSON string
   *
   * Uses PHP's internal json_decode function.
   *
   * @param Array $args the list in which to check for $key
   * @param String $key the key
   * @param String $mes the exception message upon failure
   * @return Object decoded object
   */
  final public function reqJSON(Array $args, $key, $mes = "GSE") {
    if (($json = json_decode($this->reqString($args, $key, 1, 8388608, $mes))) === null)
      $this->panic($mes, json_last_error());
    return $json;
  }

  /**
   * Requires that $args[$key] exist and represent a valid domain name
   *
   * @param Array $args the list in which to check for $key
   * @param String $key the key
   * @param String $mes the exception message upon failure
   * @return String domain name
   */
  final public function reqFQDN(Array $args, $key, $mes = "GSE") {
    $fqdn = $this->reqString($args, $key, 3, 254, $mes);
    if (preg_match('/[^A-Za-z0-9.-]/', $fqdn) > 0)
      $this->panic($mes, SoterException::DNS_CHARACTERS);
    $parts = explode(".", $fqdn);
    $cnt = count($parts);
    if ($cnt < 2 || $cnt > 127)
      $this->panic($mes, SoterException::DNS_SIZE);
    foreach ($parts as $sub) {
      $len = mb_strlen($sub);
      if ($len == 0 || $len > 63)
        $this->panic($mes, SoterException::DNS_INVALID_SUB);
      if ($sub[0] == '-' || $sub[$len - 1] == '-')
        $this->panic($mes, SoterException::DNS_INVALID_SUB);
    }
    if (preg_match('/^[0-9]+$/', $parts[$cnt - 1]) > 0)
      $this->panic($mes, SoterException::DNS_CHARACTERS);
    return $fqdn;
  }

  // ------------------------------------------------------------
  // Include wrappers
  // ------------------------------------------------------------

  /**
   * Look for an integer value in $args[$key] and return it, or the
   * default value, if no valid one found
   *
   * @see reqInt
   */
  final public function incInt(Array $args, $key, $min = 0, $max = PHP_INT_MAX, $default = 0) {
    try {
      return $this->reqInt($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incFloat(Array $args, $key, $min = 0, $max = PHP_INT_MAX, $default = 0.0) {
    try {
      return $this->reqFloat($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incKey(Array $args, $key, Array $values, $default = null) {
    try {
      return $this->reqKey($args, $key, $values);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incValue(Array $args, $key, Array $values, $default = null) {
    try {
      return $this->reqValue($args, $key, $values);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incString(Array $args, $key, $min = 0, $max = 8388608, $default = null) {
    try {
      return $this->reqString($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incRaw(Array $args, $key, $min = 0, $max = 8388608, $default = null, Array $opers = array()) {
    try {
      return $this->reqRaw($args, $key, $min, $max, "GSE", $opers);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incDBObject(Array $args, $key, $obj, $default = null) {
    try {
      return $this->reqDBObject($args, $key, $obj);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incList(Array $args, $key, $size = null, Array $default = array()) {
    try {
      return $this->reqList($args, $key, $size);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incMap(Array $args, Array $keys, $size = null, Array $default = array()) {
    try {
      return $this->reqMap($args, $keys, $size);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incDate(Array $args, $key, DateTime $min = null, DateTime $max = null, DateTime $default = null) {
    try {
      return $this->reqDate($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incFile(Array $args, $key, $min = 0, $max = 8388608, $default = null) {
    try {
      return $this->reqFile($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incFiles(Array $args, $key, $min = 0, $max = 8388608, $default = array()) {
    try {
      return $this->reqFiles($args, $key, $min, $max);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incRE(Array $args, $key, $regex, Array $default = array()) {
    try {
      return $this->reqRE($args, $key, $regex);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incEmail(Array $args, $key, $default = null) {
    try {
      return $this->reqEmail($args, $key);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incURL(Array $args, $key, $default = null) {
    try {
      return $this->reqURL($args, $key);
    }
    catch (SoterException $e) {
      return $default;
    }
  }
  
  final public function incJSON(Array $args, $key, $default = null) {
    try {
      return $this->reqJSON($args, $key);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  final public function incFQDN(Array $args, $key, $default = null) {
    try {
      return $this->reqFQDN($args, $key);
    }
    catch (SoterException $e) {
      return $default;
    }
  }

  // ------------------------------------------------------------
  // HAS wrappers
  // ------------------------------------------------------------

  /**
   * If present and valid, the value will be put in $value.
   *
   * Otherwise, false is returned and $value is not touched.
   *
   * @return boolean
   * @see reqInt
   */
  final public function hasInt(&$value, Array $args, $key, $min = 0, $max = PHP_INT_MAX) {
    try {
      $value = $this->reqInt($args, $key, $min);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasFloat(&$value, Array $args, $key, $min = 0, $max = PHP_INT_MAX) {
    try {
      $value = $this->reqFloat($args, $key, $min, $max);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasKey(&$value, Array $args, $key, Array $values) {
    try {
      $value = $this->reqKey($args, $key, $values);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasValue(&$value, Array $args, $key, Array $values) {
    try {
      $value = $this->reqValue($args, $key, $values);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasString(&$value, Array $args, $key, $min = 0, $max = 8388608) {
    try {
      $value = $this->reqString($args, $key, $min, $max);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasDBObject(&$value, Array $args, $key, $obj) {
    try {
      $value = $this->reqDBObject($args, $key, $obj);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasList(&$value, Array $args, $key, $size = null) {
    try {
      $value = $this->reqList($args, $key, $size);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasMap(&$value, Array $args, Array $keys, $size = null) {
    try {
      $value = $this->reqMap($args, $keys, $size);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasDate(&$value, Array $args, $key, DateTime $min = null, DateTime $max = null) {
    try {
      $value = $this->reqDate($args, $key, $min, $max);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasFile(&$value, Array $args, $key, $min = 0, $max = 8388608) {
    try {
      $value = $this->reqFile($args, $key, $min, $max);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasFiles(&$value, Array $args, $key, $min = 0, $max = 8388608) {
    try {
      $value = $this->reqFiles($args, $key, $min, $max);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasRE(&$value, Array $args, $key, $regex) {
    try {
      $value = $this->reqRE($args, $key, $regex);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasEmail(&$value, Array $args, $key) {
    try {
      $value = $this->reqEmail($args, $key);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasURL(&$value, Array $args, $key) {
    try {
      $value = $this->reqURL($args, $key);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasJSON(&$value, Array $args, $key) {
    try {
      $value = $this->reqJSON($args, $key);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }

  final public function hasFQDN(&$value, Array $args, $key) {
    try {
      $value = $this->reqFQDN($args, $key);
      return true;
    }
    catch (SoterException $e) {
      return false;
    }
  }
}
?>