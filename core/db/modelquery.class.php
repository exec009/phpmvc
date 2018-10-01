<?php
namespace CORE\DB;

class ModelQuery extends Query implements IQuery
{
    public $modelCustomKeys, $parentQuery;
	public static function table(string $table, string $model = '', string $alias = null, IQuery $parentQuery = null): IQuery
	{
		$self = new self($table, $model);
        $self->alias = $alias;
        $self->aliasColumn = $self->alias === null ? '' : $self->alias.'`.`';
        $self->parentQuery = $parentQuery;
		return $self;
	}
    public function selectM(array $columns): IQuery
    {
		$this->type="select";
        $cols = [];
        foreach($columns as $key => $data)
        {
            if(is_string($key))
            {
                if(is_array($data))
                {
                    $dataKey = array_keys($data)[0];
                    $cols[] = $dataKey."(".$this->getColumn($data[$dataKey]).") as `{$key}`";
                    $this->modelCustomKeys[] = $key;
                }
                else if($data instanceof IQuery)
                {
                    $cols[] = "({$data}) as `{$key}`";
                }
                else
                {
                    $this->modelCustomKeys[] = $key;
                    $cols[] = $this->getColumn($data)." as `{$key}`";
                }
            }
            else
            {
                $key = $this->getColumn($data);
                $cols[] = $key;
                $this->modelCustomKeys[] = $key;
            }
        }
        $cols = implode(',', $cols);
        if($this->alias !== null)
        $this->startquery="select ".$cols." from ".$this->table." as `{$this->alias}`";
        else
        $this->startquery="select ".$cols." from ".$this->table;
        $this->modelSoftClass = true;
        return $this;
    }
	public function groupBy(string ...$group): IQuery
	{
        foreach($group as $key => $data)
        {
            $group[$key] = $this->getColumn($data);
        }
		if(count($group) > 0)
		{
			$this->group=' group by '.implode(',',$group);
		}
		return $this;
	}
	public function orderBy(array $order): IQuery
	{
		$this->order=' order by ';
		$orders=[];
		foreach($order as $key=>$data)
		{
            try
            {
                $key1 = $this->getColumn($key);
            }
            catch(\CORE\MVC\ModelErrorException $e)
            {
                if(!in_array($key, $this->modelCustomKeys))
                throw $e;
            }
            $orders[] = $key1.' '.$data.' ';
		}
        $this->order.=implode(', ',$orders);
		return $this;
    }
    public function having(...$clauses): IQuery
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
                try
                {
                    $data[0] = $this->getColumn($data[0]); // model line
                    if(!is_int($data[2]))
                        $data[2]=$this->normalizeStringValue($data[2]); // '"'.$data[2].'"';
                    if(($data[3]??true)==true)
                        $data[0]=$data[0];
                }
                catch(\CORE\MVC\ModelErrorException $e)
                {
                    if(!in_array($data[0], $this->modelCustomKeys))
                    throw $e;
                    else
                    {
                        if(!is_int($data[2]))
                            $data[2]='"'.$data[2].'"';
                        if(($data[3]??true)==true)
                            $data[0]='`'.$data[0].'`';
                    }
                }

				$this->having_clause.=$data[0].$data[1].$data[2]." ";
			}
			else
                $this->having_clause.=$data." ";
		}
		$this->having_clause=$this->having_clause;
		return $this;
    }
    private function parentWhere(...$clauses): IQuery
    {
         return $this->where($clauses);
    }
	public function where(...$clauses): IQuery
	{
        return $this->whereCore($clauses);
	}
    protected function whereCore(...$clauses): IQuery
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
                    $this->clause.=' match(`'.implode("`,`",$this->getColumns($data[0]))."`) against('".$data[2]."' IN BOOLEAN MODE) ";
				}
                else if ($data[1] == 'in' || $data[1] == '!in')
                {
                    $data[0] = $this->getColumn($data[0]);
                    if($data[2] instanceof IQuery)
                    {
                        if($data[1] == 'in')
                        $this->clause.="$data[0] in ({$data[2]})";
                        else
                        $this->clause.="$data[0] not in ({$data[2]})";
                    }
                    else
                    {
                        if(is_object($data[2][0] ?? null))
                        {
                            foreach($data[2] as $_kts => $_dts)
                            {
                                $data[2][$_kts] = $_dts->getId();
                            }
                        }
                        if($data[1] == 'in')
                        $this->clause.="$data[0] in ('".implode("','", $data[2])."')";
                        else
                        $this->clause.="$data[0] not in ('".implode("','", $data[2])."')";
                    }
                }
                else if ($data[1] === 'between')
                {
                    $data[0] = $this->getColumn($data[0]);
                    $this->clause .= "$data[0] between '".$data[2][0]."' and  '".$data[2][1]."'";
                }
				else
				{
					if(!is_int($data[2]) && ($data[3]??true)==true)
                        $data[2]=$this->normalizeStringValue($data[2]);
                    $maincol=$this->getColumn($data[0]);
                    if(($data[4]??true)==true)
                        $maincol=$this->getColumn($data[0]);
                    $this->clause.=$maincol.$data[1].$data[2]." ";
				}
			}
			else
                $this->clause.=$data." ";
		}
		$this->clause=$this->clause;
		return $this;
    }
    protected function getColumn(string $column): string
    {
        $cl = explode('.', $column);
        if(count($cl) > 1)
        {
            if($cl[0] === $this->alias)
            return "`{$this->aliasColumn}".(('\\'.$this->model)::getDBColumn($cl[1])).'`';
            else
            return $column;
        }
        else
        return "`{$this->aliasColumn}".(('\\'.$this->model)::getDBColumn($column)).'`';
    }
    private function normalizeStringValue(string $value): string
    {
        $cl = explode('.', $value);
        if($this->parentQuery !== null && count($cl) == 2 && $cl[0] === $this->parentQuery->alias)
        {
            try
            {
                $cl[1] = $this->parentQuery->getColumn($cl[1]);
                return $cl[1];
            }
            catch(\CORE\MVC\ModelErrorException $e)
            {
                return '"'.$value.'"';
            }
        }
        else
        return '"'.$value.'"';
    }
}
