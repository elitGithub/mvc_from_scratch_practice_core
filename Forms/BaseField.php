<?php


namespace eligithub\phpmvc\Forms;


use eligithub\phpmvc\Model;

abstract class BaseField implements \Stringable
{
	abstract public function renderInput(): string;

	public function __construct(public Model $model, public string $attribute)
	{

	}

	public function __toString(): string
	{
		return sprintf('
			<div class="form-group">
			<label for="%s"> %s </label>
			%s
				<div class="invalid-feedback">%s</div>
			</div>',
			$this->attribute,
			$this->model->getLabel($this->attribute),
			$this->renderInput(),
			$this->model->getFirstError($this->attribute));
	}
}