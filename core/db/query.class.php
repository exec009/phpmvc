<?php
namespace CORE\DB;
class Query
{
	public $table,$model,$isModel,$query,$clause,$order,$limit,$startquery,$group,$type,$having_clause;
	public static function table(string $table, string $model = '')
	{
		$self = new self($table, $model);
		return $self;
	}
    public function __construct(string $table, string $model)
    {
		$this->clause = "";
		$this->table = $table;
        $this->model = $model;
        $this->isModel = !empty($model);
    }
	public function select(string ...$cols):self
	{
        if($this->isModel)
        {
            foreach($cols as $key => $data)
            {
                if($data != "*")
                {
                    $dts = explode(".", $data);
                    if(count($dts) < 2)
                    $cols[$key] = $this->getColumn($data);
                    else
                    $cols[$key] = $dts[0].".".$this->getColumn($dts[1]);
                }
            }
        }
		if(count($cols)>0)
		$cols=implode(',',$cols);
		else
		$cols='*';
		$this->startquery="select ".$cols." from ".$this->table;
		$this->type="select";
		return $this;
	}
    private function getColumn(string $column) : string
    {
        return ('\\'.$this->model)::getDBColumn($column);
    }
	private function getColumns(array $columns) : array
    {
		foreach ($columns as $key => $value) {
			$columns[$key] = $this->getColumn($value);
		}
        return $columns;
    }
    public function toList() : array
    {
        if(!$this->isModel)
        return $this->run()->fetchObject();
        else
        {
            $result = [];
            $objects = $this->select()->run()->fetchArray();
            foreach($objects as $data)
            {
                $result[] = ($this->model)::init($data);
            }
            return $result;
        }
    }
	public function countTotal(): self
	{
		$this->startquery = "select SQL_CALC_FOUND_ROWS ".substr($this->startquery, 6, strlen($this->startquery));
		return $this;
	}
	public function getFoundRows(): int
	{
		$result = DB::select('SELECT FOUND_ROWS() as total')->fetchObject()[0];
		return $result->total;
	}
	public function processForDataTable(array $columns): array
	{
		$cols = [];
		foreach($columns as $key => $col)
		{
			$cols[] = is_int($key) ? $col : $key;
		}
		$result = [];
		$objects = $this->select(($this->model)::getPrimaryKey())->countTotal()->limit(intval($_GET['start'] ?? 0), intval($_GET['length'] ?? 10));
		try
		{
			$this->getColumn($cols[$_GET['order'][0]['column']]);
			$objects->orderBy([$cols[$_GET['order'][0]['column']] => $_GET['order'][0]['dir'] == 'asc' ? 'asc' : 'desc']);
		}
		catch(\Exeception $e)
		{
			$objects->orderBy(['Id' => $_GET['order'][0]['dir'] == 'asc' ? 'asc' : 'desc']);
		}
		if(!empty($_GET['search']['value']))
		{
			$params = [];
			foreach($cols as $col)
			{
				try
				{
					$this->getColumn($col);
					$params[] = [$col, 'like', $_GET['search']['value'].'%'];
					$params[] = _OR_;
				}
				catch(\Exception $e)
				{

				}
			}
			$foreignKey = ($this->model)::getForeignKeys();
			foreach($foreignKey as $keyName => $_key)
			{
				$__key = explode(":",$_key['ForeignKey']);
				$_cols = ($__key[0])::getColumnNames();
				$_params = [];
				foreach($_cols as $_col)
				{
					$_params[] = [$_col, ' like ', $_GET['search']['value']."%"];
					$_params[] = _OR_;
				}
				array_pop($_params);
				$list = ($__key[0])::find()->where($_params)->toList();
				foreach($list as $cur)
				{
					$params[] = [$keyName, '=', $cur->{"get".$__key[1]}()];
					$params[] = _OR_;
				}
			}
			array_pop($params);
			if(strlen($this->clause)>=4)
			$this->clause .= " and ";
			$objects = $objects->where($params);
		}
		$objects = $objects->run()->fetchObject();
		$total = $this->getFoundRows();
		foreach($objects as $data)
		{
			$model = ($this->model)::init($data->{($this->model)::getDBColumn(($this->model)::getPrimaryKey())});
			$rs = [];
			foreach($columns as $key => $col)
			{
				if(is_int($key))
				$rs[] = $model->{"get".ucfirst($col)}();
				else
				{
					if(is_callable($col))
					{
						$func = new \ReflectionFunction($col);
						$params = $func->getParameters();
						$prms = [];
						foreach($params as $dtr)
						{
							$prms[] = $model->{"get".ucfirst($dtr->getName())}();
						}
						$rs[] = call_user_func_array($col, $prms);
					}
					else
					{
						preg_match_all('/({{)[A-Z,a-z]{1,}(}})/', $col, $m);
						$finalStr = $col;
						foreach($m[0] as $__key)
						{
							$__key = substr($__key, 2, strlen($__key)-4);
							$finalStr = str_replace("{{".$__key."}}", $model->{"get".ucfirst($__key)}(), $finalStr);
						}
						$rs[] = $finalStr;
					}
				}
			}
			$result[] = $rs;
		}
        $output = [
            'draw' => $_GET['draw'] ?? 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result
        ];
		return $output;
	}
    public function single()
    {
        if(!$this->isModel)
            return $this->run()->getObject();
        else
        {
            $result = null;
            $object = $this->select()->run()->getArray();
            if($object != null)
            $result = ($this->model)::init($object);
            return $result;
        }
    }
	public function update(array $array):self
	{
		$cols=[];
		foreach($array as $key=>$data)
		{
			if(!is_int($data))
			$data='"'.$data.'"';
			$cols[]=$key."=".$data.' ';
		}
		$this->startquery=" update ". $this->table." set ".implode(' , ',$cols);
		$this->type="update";
		return $this;
	}
	public function insert(array $array):self
	{
		$cols=implode(",",array_keys($array));
		foreach($array as $key => $val)
		{
			if(!is_int($val) && !is_float($val))
			$array[$key] = "'".$val."'";
		}
		$vals=implode(",",$array);
		$this->startquery=" insert into ". $this->table." (".$cols.") values(".$vals.")";
		$this->type="insert";
		return $this;
	}
	public function delete():self
	{

		$this->startquery="delete from ".$this->table;
		$this->type="delete";
		return $this;
	}
	public function orderBy(array $order):self
	{
		$this->order=' order by ';
		$orders=[];
		foreach($order as $key=>$data)
		{
            if($this->isModel)
                $key = $this->getColumn($key);
			$orders[]=$key.' '.$data.' ';
		}
		$this->order.=implode(', ',$orders);
		return $this;
	}
	public function orderByRandom():self
	{
		$this->order=' order by rand() ';
		return $this;
	}
    private function parentWhere(...$clauses) : self
    {
         return $this->where($clauses);
    }
	public function where(...$clauses) : self
	{
        return $this->whereCore($clauses);
	}
    protected function fixArray($ar) : array
    {
        return count($ar) == 1 ? $ar[0] : $ar;
    }
    protected function whereCore(...$clauses) : self
	{
		if(strlen($this->clause)<4)
			$this->clause=" where ";

		foreach($clauses as $key=>$data)
		{
            if(is_array($data))
            $data = $this->fixArray($data);
			if(is_array($data[0]) && $data[1]!="match")
			{
				$this->clause.=' ( ';
				foreach($data as $dt)
                    $this->whereCore($dt);
				$this->clause.=' ) ';
			}
			else if(is_array($data))
			{
				if($data[1]=="match")
				{
                    if($this->isModel)
                        $this->clause.=' match(`'.implode("`,`",$this->getColumns($data[0]))."`) against('".$data[2]."' IN BOOLEAN MODE) ";
                    else
    					$this->clause.=' match(`'.implode("`,`",$data[0])."`) against('".$data[2]."' IN BOOLEAN MODE) ";
				}
                else if ($data[1] == 'in')
                {
                    if($this->isModel)
                    {
                        $data[0] = $this->getColumn($data[0]);
                    }
                    if($this->isModel && is_object($data[2][0] ?? null))
                    {
                        foreach($data[2] as $_kts => $_dts)
                        {
                            $data[2][$_kts] = $_dts->getId();
                        }
                    }
                    $this->clause.="`$data[0]` in ('".implode("','", $data[2])."')";
                }
				else
				{
					if(!is_int($data[2]) && ($data[3]??true)==true)
	                    $data[2]='"'.$data[2].'"';

                    if($this->isModel)
                    {
                        $maincol=$this->getColumn($data[0]);
                        if(($data[4]??true)==true)
                            $maincol='`'.$this->getColumn($data[0]).'`';

                        $this->clause.=$maincol.$data[1].$data[2]." ";
                    }
                    else
                    {
                        $maincol=$data[0];
                        if(($data[4]??true)==true)
                            $maincol='`'.$data[0].'`';

                        $this->clause.=$maincol.$data[1].$data[2]." ";
                    }
				}
			}
			else
                $this->clause.=$data." ";
		}
		$this->clause=$this->clause;
		return $this;
	}
	public function having(...$clauses):self
	{
		if(strlen($this->having_clause)<4)
			$this->having_clause=" having ";

		foreach($clauses as $key=>$data)
		{

			if(is_array($data[0]))
			{
				$this->having_clause.=' ( ';
				foreach($data as $dt)
                    $this->having($dt);
				$this->having_clause.=' ) ';
			}
			else if(is_array($data))
			{
                if($this->isModel)
                {
                    $data[0] = $this->getColumn($data[0]);
                }
				if(!is_int($data[2]))
                    $data[2]='"'.$data[2].'"';
                if(($data[3]??true)==true)
                	$data[0]='`'.$data[0].'`';

				$this->having_clause.=$data[0].$data[1].$data[2]." ";
			}
			else
                $this->having_clause.=$data." ";
		}
		$this->having_clause=$this->having_clause;
		return $this;
	}
	public function limit(int $l1=0,int $l2):self
	{
		$this->limit=" limit ".$l1.", ".$l2;
		return $this;
	}
	public function groupBy(string ...$group)
	{
        if($this->isModel)
        {
            foreach($group as $key => $data)
            {
                $group[$key] = $this->getColumn($data);
            }
        }
		if(count($group)>0)
		{
			$this->group=' group by '.implode(',',$group);
		}
		return $this;
	}
	public function run($runItLaterIfConnectionFails = false)
	{
        try
        {
            $result = DB::{$this->type}($this->get());
        }
        catch(IDBException $e)
        {
            if($runItLaterIfConnectionFails)
            \CORE\TaskRunner::addTask("\CORE\DB\DB::".$this->type, $this->get());
            throw $e;
        }
        return $result;
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
            $query=$this->startquery.' '.$this->clause.' '.$this->group.' '.$this->having_clause.' '.$this->order.' '.$this->limit;
        }
		return $query;
    }
    public function getAs(string $columnName)
    {
        $query="(".$this->get().") as `$columnName`";
		return $query;
    }
    public function __toString()
    {
        return $this->get();
    }
}
//$currency=[['col1','col2','col3'], 'match', "matchThis*"];
//$usr=Query::table('users')->where(['id','=',3])->orderBy(['status'=>'asc','id'=>'desc'])->groupBy('id','status')->limit(0,100)->select('id','name')->run();
//echo $usr=Query::table('users')->where(['id','=',3])->orderBy(['status'=>'asc','id'=>'desc'])->groupBy('id','status')->limit(0,100)->insert(['id'=>2,'name'=>'Adam Smith'])->run();
//$usr=Query::table('users')->delete()->where(['id','=',3])->limit(0,1)->run();
//echo $usr;
/*
$query1=Query::table("currency_stats")->where(['currency_id','=','`currency_info`.`id`'])->orderBy(['id','desc'])->limit(0,1);
Query::table('users')->where()->having()->select('*',$query1->getAs('current_value'));

$usr->where(
	['id','=',3],
	_AND_,
	[
		['status','=',3],
		_OR_,
		[
			['status','=',4],
			_AND_,
			['status','>',2]
		]
	]
);
echo $usr->clause;
*/

/*
where
(id=0 and game=1)
or
(id=1 and game=2)

where([

['id','=',1],
_AND_,
[
['status','=',1],
_OR_,
['amount','>','90238899']
]
])

where id=1 and (status=1 and amount=9898484)

where(
['id','=',1]
)
orderBy(['id'=>asc,'status'=>desc])
 */
