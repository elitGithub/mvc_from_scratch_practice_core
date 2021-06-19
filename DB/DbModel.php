<?php


namespace App\Core\DB;


use App\Core\Application;
use App\Core\Model;
use PDOException;
use PDOStatement;

abstract class DbModel extends Model
{
	abstract public static function tableName(): string;

	abstract public function attributes(): array;

	abstract public static function primaryKey(): string;

	public function save(): bool
	{
		try {
			$tableName = static::tableName();
			$attributes = $this->attributes();
			$params = array_map(fn($attr) => ":$attr", $attributes);
			$statement = static::prepare("INSERT INTO $tableName (" . implode(',',
					$attributes) . ") VALUES (" . implode(',', $params) . ");");
			foreach ($attributes as $attribute) {
				$statement->bindValue(":$attribute", $this->{$attribute});
			}
			$statement->execute();
			return true;
		} catch (PDOException $e) {
			if (method_exists($this, 'logErrors')) {
				// TODO: add log!
				$this->logErrors($e->getMessage(), $e->getTraceAsString());
			}
			return false;
		}
	}

	public static function prepare($sql): bool|PDOStatement
	{
		return Application::$app->db->pdo->prepare($sql);
	}

	public static function findOne(array $where = [])
	{
		$tableName = static::tableName();
		$attributes = array_keys($where);
		$sqlWhere = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
		$stmt = static::prepare("SELECT * FROM $tableName WHERE $sqlWhere");
		foreach ($where as $key => $value) {
			$stmt->bindValue(":$key", $value);
		}

		$stmt->execute();
		return $stmt->fetchObject(static::class);
	}
}