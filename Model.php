<?php


namespace eligithub\phpmvc;


abstract class Model
{

	public const RULE_REQUIRED = 'required';
	public const RULE_EMAIL = 'email';
	public const RULE_MIN = 'min';
	public const RULE_MAX = 'max';
	public const RULE_MATCH = 'match';
	public const RULE_UNIQUE = 'unique';

	public array $errors = [];

	public function loadData(array $data)
	{
		foreach ($data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * @return array
	 */
	abstract public function rules(): array;

	public function labels(): array
	{
		return [];
	}

	public function getLabel($attribute)
	{
		return $this->labels()[$attribute] ?? $attribute;
	}

	/**
	 * @return bool
	 */
	public function validate(): bool
	{
		foreach ($this->rules() as $attribute => $rules) {
			$value = $this->{$attribute};
			foreach ($rules as $rule) {
				$ruleName = $rule;
				if (!is_string($ruleName)) {
					$ruleName = $rule[0];
				}

				if ($ruleName === static::RULE_REQUIRED && !$value) {
					$this->addErrorForRule($attribute, static::RULE_REQUIRED, $rule);
				}

				if ($ruleName === static::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$this->addErrorForRule($attribute, static::RULE_EMAIL, $rule);
				}

				if ($ruleName === static::RULE_MIN && (strlen($value) < $rule[static::RULE_MIN])) {
					$this->addErrorForRule($attribute, static::RULE_MIN, $rule);
				}

				if ($ruleName === static::RULE_MAX && (strlen($value) > $rule[static::RULE_MAX])) {
					$this->addErrorForRule($attribute, static::RULE_MAX, $rule);
				}

				if ($ruleName === static::RULE_MATCH && $value !== $this->{$rule['match']}) {
					$rule['match'] = $this->getLabel($rule['match']);
					$this->addErrorForRule($attribute, static::RULE_MATCH, $rule);
				}

				if ($ruleName === static::RULE_UNIQUE) {
					$className = $rule['class'];
					$uniqueAttr = $rule['attribute'] ?? $attribute;
					$tableName = $className::tableName();
					$stmt = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
					$stmt->bindValue(":attr", $value);
					$stmt->execute();
					$record = $stmt->fetchObject();
					if ($record) {
						$this->addErrorForRule($attribute, static::RULE_UNIQUE,
							['field' => $this->getLabel($attribute)]);
					}
				}
			}
		}

		return empty($this->errors);
	}

	/**
	 * @param  string  $attribute
	 * @param  string  $rule
	 * @param  array|string  $params
	 */
	public function addErrorForRule(string $attribute, string $rule, array|string $params = [])
	{
		$message = $this->errorMessages()[$rule] ?? '';
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$message = str_replace('{' . $key . '}', $value, $message);
			}
		}
		$this->errors[$attribute][] = $message;
	}


	public function addError(string $attribute, string $message)
	{
		$this->errors[$attribute][] = $message;
	}

	/**
	 * @return string[]
	 */
	public function errorMessages(): array
	{
		return [
			static::RULE_REQUIRED => 'This field is required.',
			static::RULE_EMAIL    => 'This field must be a valid email address.',
			static::RULE_MIN      => 'This field must be at least {min} characters long.',
			static::RULE_MAX      => 'This field must be at most {max} characters long.',
			static::RULE_MATCH    => 'This field must be the same as {match}.',
			static::RULE_UNIQUE   => 'Record with this {field} already exists.',
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return mixed
	 */
	public function hasError($attribute): mixed
	{
		return $this->errors[$attribute] ?? false;
	}

	/**
	 * @param $attribute
	 *
	 * @return mixed
	 */
	public function getFirstError($attribute): mixed
	{
		return $this->errors[$attribute][0] ?? '';
	}
}