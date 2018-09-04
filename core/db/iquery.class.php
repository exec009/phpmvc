<?php
namespace CORE\DB;
interface IQuery {
    function groupBy(string ...$group): self;
    function orderBy(array $order): self;
    function having(...$clauses): self;
	function where(...$clauses): IQuery;
}