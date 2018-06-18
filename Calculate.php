#!/usr/bin/env php
<?php

require_once dirname(__FILE__).'/Command/AbstractCommand.php';
require_once dirname(__FILE__).'/Math/Calculator.php';

new \PhpConsole\Math\Calculator(isset($argv) ? $argv : null);
