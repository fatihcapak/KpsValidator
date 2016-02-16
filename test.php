<?php
require_once 'KpsValidator.php';

var_dump(KpsValidator::getInstance()->check('12345678901', 'NAME', 'LASTNAME', '1970'));
