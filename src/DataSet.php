<?php
namespace jLib;

class DataSet
{
	private $data     = array();
	private $count    = 0;
	private $total    = 0;
	private $page     = 1;
	private $pages    = 1;
	private $pageSize = 5;
	private $filter   = NULL;
	private $sort     = NULL;
	private $args     = NULL;
	private $model    = NULL;
	private $sql      = NULL;
	private $filterList = array();

	public function addFilter ($filter)
	{
		$this->filterList[] = $filter;
		return $this;
	}
	public function execute ()
	{
		//if (is_null($sql))
		//{
			//throw new Execp'Informe a query SQL!';
		//}

		$Page = Page::getInstance();
		
		$p = $Page->getParams();

		//_pre($p);

		$this->page = $p['p'];

		$start = ($this->page - 1) * $this->pageSize;

		$query = new Query($this->sql);
		$query->setLimit($start, $this->pageSize);

		foreach ($this->filterList as $f)
		{
			//$query->addWhere($f->getField() .  $f->getCondition . $f->getValue());
			$query->addAnd($f->render());
		}

		if (count($Page->getSort()) > 0)
		{
			//_pre(Page::getSort());
			$query->setSort($Page->getSort());
		}

		$query->run();

		$this->count = $query->getCount();
		$this->data  = $query->getData();
		$this->total = $query->getTotal();
		$this->pages = ceil($this->count / $this->pageSize);
	}
	public function getArgs ()
	{
		return $this->args;
	}
	public function getCount ()
	{
		return $this->count;
	}
	public function getData ($index = NULL)
	{
		if (is_null($index))
		{
			return $this->data;
		}

		if (isset($this->data[$index]))
		{
			return $this->data[$index];
		}

		return NULL;
	}
	public function getFilter ()
	{
		return $this->filter;
	}
	public function getFilterList ()
	{
		return $this->filterList;
	}
	public function getModel ()
	{
		return $this->model;
	}
	public function getPage ()
	{
		return $this->page;
	}
	public function getPages ()
	{
		return $this->pages;
	}
	public function getPageSize ()
	{
		return $this->pageSize;
	}
	public function getSort ()
	{
		return $this->sort;
	}
	public function getTotal ()
	{
		return $this->total;
	}
	public function setArgs ($args)
	{
		$this->args = $args;
		return $this;
	}
	public function setCount ($count)
	{
		$this->count = $count;
		return $this;
	}
	public function setData ($data)
	{
		$this->data = $data;
		return $this;
	}
	public function setFilter ($filter)
	{
		$this->filter = $filter;
		return $this;
	}
	public function setModel ($model)
	{
		$this->model = $model;
		return $this;
	}
	public function setPage ($page)
	{
		$this->page = $page;
		return $this;
	}
	public function setPages ($pages)
	{
		$this->pages = $pages;
		return $this;
	}
	public function setPageSize ($pageSize)
	{
		$this->pageSize = $pageSize;
		return $this;
	}
	public function setSql ($sql)
	{
		$this->sql = $sql;
		return $this;
	}
	public function setSort ($sort)
	{
		$this->sort = $sort;
		return $this;
	}
	public function setTotal ($total)
	{
		$this->total = $total;
		return $this;
	}

}