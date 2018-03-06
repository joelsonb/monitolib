<?php
namespace MonitoLib\Database;

interface Dao
{
	public function bulkInsert ($dtoList, $split = 1000);
	public function count ();
	public function delete ($dto);
	// public function deleteById ($id);
	public function execute ($sql);
	public function getById ($id);
	public function getByFilter ($filter = null);
	public function getBySql ($sql);
	public function getLastId ();
	public function insert ($dto);
	public function list ($filter = null);
	public function listAll ($filter = null);
	public function listBySql ($sql);
	public function query ($sql);
	public function truncate ();
	public function update ($dto);
}