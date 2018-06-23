<?php
namespace CORE\MODELS;
use CORE\DB\DB;
use CORE\MVC\Model;
use CORE\MVC\IModel;
class Status extends \CORE\Enum
{
    const Success = 1;
    const Failed = 2;
}