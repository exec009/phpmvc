<?php
namespace MODELS\ADMIN;
use CORE\MVC\Model;
use CORE\MVC\IModel;
class Status extends \CORE\Enum
{
    const Active = 1;
    const Disabled = 2;
}