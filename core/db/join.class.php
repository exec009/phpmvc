<?php
namespace CORE\DB;
use \stdClass;
class Join extends Query
{
    private $class;
    private $property;
    private $suffix;
    private $classSuffix;
    private $joinClause;
    public function __construct(string $class, string $suffix, string $property)
    {
        parent::__construct($class::getTableName().' as '.$suffix, '');
        $this->class = $class;
        $this->suffix = $suffix;
        $this->property = $property;
        $this->classSuffix = [];
        $this->classSuffix [$suffix] = $class;
        $this->joinClause = '';
    }
    public function join(string $columnMapping, self $join) : self
    {
        $this->classSuffix[$join->suffix] = $join->class;
        $columns = explode(".", $columnMapping);
        if(count($columns) < 2)
        throw new \InvalidArgumentException("Invalid Argument.");
//      $columnMapping = $columns[0].".".$this->class::getDBColumn($columns[1]);
        $columnMapping = $columns[0].".".$this->classSuffix[$columns[0]]::getDBColumn($columns[1]);
        $this->joinClause .= " inner join {$join->class::getTableName()} as {$join->suffix}
                               on {$join->suffix}.{$join->class::getDBColumn($join->property)}={$columnMapping}";
        return $this;
    }
    public function get()
    {
        if($this->type == "update" || $this->type == "insert")
        {
            if(!empty($this->limit))
            {
                throw new IncorrectQueryException("Use of limit function on ".$this->type." query is not allowed.");
            }
        }
		if($this->type=="insert")
        {
            $query=$this->startquery;
        }
		else
        {
            $query=$this->startquery.' '.$this->joinClause.' '.$this->clause.' '.$this->group.' '.$this->having_clause.' '.$this->order.' '.$this->limit;
        }
		return $query;
    }
	private function whereArray(...$clauses) : array
    {
        $clauses = $this->fixArray($clauses);
		foreach($clauses as $key=>$data)
		{
            if(isset($data[2]) && is_object($data[2]))
            {
                $clauses[$key][2] = $data[2]->getId();
            }
			if(is_array($data[0]))
            {
                $clauses[$key] = $this->where($data);
            }
            else if($data != _AND_ && $data != _OR_)
            {
                $fix = explode(".", $data[0]);
                try
                {
                    $fix = $fix[0].'.'.$this->classSuffix[$fix[0]]::getDbColumn($fix[1]);
                }
                catch(\Exception $e)
                {
                }
                try
                {
                    $clauses[$key][0] = $fix;
                }
                catch(\Exception $e)
                {
                }
                if(!isset($clauses[$key][3]))
                    $clauses[$key][3] = true;
                $clauses[$key][4] = false;
            }
        }
        //        print_r($clauses);
        //        exit();
        return $clauses;
    }
	public function where(...$clauses) : Query
    {
		foreach($clauses as $key=>$data)
		{
            if(isset($data[2]) && is_object($data[2]))
            {
                $clauses[$key][2] = $data[2]->getId();
            }
			if(is_array($data[0]))
            {
                $clauses[$key] = $this->whereArray($data);
            }
            else if($data != _AND_ && $data != _OR_)
            {
                $fix = explode(".", $data[0]);
                try
                {
                    $fix = $fix[0].'.'.$this->classSuffix[$fix[0]]::getDbColumn($fix[1]);
                }
                catch(\Exception $e)
                {
                }
                try
                {
                    $clauses[$key][0] = $fix;
                }
                catch(\Exception $e)
                {
                }
                if(!isset($clauses[$key][3]))
                    $clauses[$key][3] = true;
                $clauses[$key][4] = false;
            }
        }
        //        print_r($clauses);
//        exit();
        parent::whereCore($clauses);
        return $this;
    }
    public function parentWhere(...$clauses) : self
    {
        parent::where($clauses);
    }
    public function select(string ...$cols) : Query
	{
        foreach($cols as $key=>$col)
        {
            if(in_array($col, $this->classSuffix))
                $cols[$key] = $col.".*";
            else
            {
                $col = explode(".", $col);
                $cols[$key] = $col[0].".".$this->classSuffix[$col[0]]::getDBColumn($col[1]);
            }
        }
        parent::select(...$cols);
		return $this;
	}
}
/*
\MODELS\USER\User::selectAs('a')
    ->join('a.ID',\MODELS\USER\Wallet::selectAs('b.UserId'))
    ->leftJoin('b.ID',\MODELS\USER\Wallet::selectAs('c.WalletId'))
    ->where(['C.Id','=','221'])
->select(new class {
    public $userId = "a.UserId";
    public $name = "b.Name";
})->toList();
*/