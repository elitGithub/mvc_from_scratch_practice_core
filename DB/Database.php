<?php


namespace App\Core\DB;


use PDO;
use PDOStatement;

/**
 * Class Database
 * @package App\Core\DB
 */
class Database
{

	public PDO $pdo;

	public int $batch = 1;
	private array $skipMigrations = ['.', '..'];

	public function __construct(array $config)
	{
		$dsn = $config['dsn'] ?? '';
		$user = $config['user'] ?? '';
		$password = $config['password'] ?? '';
		$this->pdo = new PDO($dsn, $user, $password);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function applyMigrations()
	{
		$this->currentBatch();
		$this->createMigrationsTable();
		$appliedMigrations = $this->getAppliedMigrations();

		$files = scandir(Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'migrations');

		$toApplyMigrations = array_diff($files, $appliedMigrations);
		foreach ($toApplyMigrations as $migration) {
			if (in_array($migration, $this->skipMigrations)) {
				continue;
			}

			require_once Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $migration;
			$className = pathinfo($migration, PATHINFO_FILENAME);
			$this->addNameSpace($className);
			$instance = new $className();

			$this->consoleOutput("Migrating $migration");
			$instance->up();
			$this->consoleOutput("Migrated $migration");
			$newMigrations[] = $migration;
		}

		if (!empty($newMigrations)) {
			$this->saveMigrations($newMigrations);
		} else {
			$this->consoleOutput("No new migrations to apply");
		}
	}

	public function reverseMigrations()
	{
		$result = $this->pdo->prepare('SELECT migration FROM migrations WHERE batch IN (SELECT MAX(batch) AS batch FROM migrations);');
		$result->execute();
		$migrations = $result->fetchAll(PDO::FETCH_COLUMN);

		foreach ($migrations as $migration) {
			require_once Application::$ROOT_DIR . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $migration;
			$className = pathinfo($migration, PATHINFO_FILENAME);
			$this->addNameSpace($className);
			$instance = new $className();

			$this->consoleOutput("Rolling Back $migration");
			$instance->down();
			$this->consoleOutput("Rolled Back $migration");
			$reversedMigrations[] = $migration;
		}

		if (!empty($reversedMigrations)) {
			$this->deleteMigrations($reversedMigrations);
		} else {
			$this->consoleOutput('No migrations to roll back');
		}
	}

	public function createMigrationsTable()
	{
		$this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    			id INT AUTO_INCREMENT PRIMARY KEY,
    			migration VARCHAR(255),
    			batch INT,
    			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
    			ENGINE=INNODB");
	}

	protected function getAppliedMigrations(): array
	{
		$statement = $this->pdo->prepare("SELECT migration FROM migrations;");
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}

	protected function addNameSpace(array|string &$className)
	{
		if (is_string($className)) {
			$className = "\\App\Migrations\\$className";
		}
	}

	protected function saveMigrations(array $migrations)
	{
		$values = join(',', array_map(fn($m) => "('$m', $this->batch)", $migrations));
		$stmt = $this->pdo->prepare("INSERT INTO migrations (migration, batch) VALUES $values");
		$stmt->execute();
	}

	protected function deleteMigrations(array $migrations) {
		$toDelete = join(',', array_map(fn($m) => "'$m'", $migrations));
		$stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration IN ($toDelete)");
		$stmt->execute();
	}

	protected function consoleOutput(string $message)
	{
		echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
	}

	public function prepare(string $query): bool|PDOStatement
	{
		return $this->pdo->prepare($query);
	}


	private function currentBatch()
	{
		$stmt = $this->pdo->prepare('SELECT MAX(batch) as batch FROM migrations');
		$stmt->execute();
		$max = $stmt->fetch(PDO::FETCH_ASSOC)['batch'];
		$this->batch = ++$max;
	}
}