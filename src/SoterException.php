<?php
namespace Soter;
/**
 * Exceptions during validation
 *
 * @author Dayan Paez
 * @version 2011-05-17
 * @package validation
 */
class SoterException extends \Exception {
  /**
   * @const all that could possibly go wrong
   */
  const INT_MISS = 1;
  const INT_NONE = 2;
  const INT_LESS = 3;
  const INT_MORE = 4;

  const FLT_MISS = 11;
  const FLT_NONE = 12;
  const FLT_LESS = 13;
  const FLT_MORE = 14;

  const KEY_MISS = 21;
  const KEY_NONE = 22;

  const VAL_MISS = 31;
  const VAL_NONE = 32;

  const STR_MISS = 41;
  const STR_NONE = 42;
  const STR_LESS = 43;
  const STR_MORE = 44;
  const STR_CODE = 45;

  const DBO_MISS = 51;
  const DBO_NONE = 52;

  const LST_MISS = 61;
  const LST_NONE = 62;
  const LST_SIZE = 63;

  const DATE_MISS = 81;
  const DATE_NONE = 82;
  const DATE_LESS = 83;
  const DATE_MORE = 84;

  const FILE_MISS = 91;
  const FILE_NONE = 92;
  const FILE_NOERROR = 93;
  const FILE_LESS = 94;
  const FILE_MORE = 95;
  const FILE_ERROR = 96;

  const RE_MISS = 101;
  const RE_NONE = 102;
  const RE_MATCH = 103;

  const EMAIL_MISS = 111;
  const EMAIL_NONE = 112;

  const URL_MISS = 121;
  const URL_NONE = 122;

  const DNS_MISS = 131;
  const DNS_NONE = 132;
  const DNS_SIZE = 133;
  const DNS_INVALID_SUB = 134;
  const DNS_CHARACTERS = 135;
}
?>