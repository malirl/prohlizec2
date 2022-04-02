<?php

class EmployeeModelDetail extends EmployeeModel
{
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->keys = $params[keys];
        $this->params = $params;
    }

    public function getFromGet() : self
    {
        $employee = new self();
        $employee->employee_id = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
        return $employee;
    }

    public function getById()
    {
        $model = parent::getById();

        $stmt = DB::getConnection()->prepare("SELECT name FROM `room` WHERE room_id=:room_id");
        $stmt->bindParam(':room_id', $model[room]);
        $stmt->execute();
        $model[room] = $stmt->fetch()->name;

        $stmt = DB::getConnection()->prepare("SELECT name, room_id FROM `key` k JOIN room r on k.room = r.room_id AND k.employee=:employee_id ORDER BY name");
        $stmt->bindParam(':employee_id', $this->employee_id);
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $keys[] = array("room_id" => $row->room_id, "name" => $row->name);
        }
        $model[keys] = $keys;

        return new self($model);
    }

    public function validate() : bool
    {
        return true;
        $isOk = true;
        $errors = [];

        if ($this->room_id === null || $this->$room_id === false) {
            $isOk = false;
            $errors["room_id"] = "Room id cannot be empty";
        }

        $this->validationErrors = $errors;
        return $isOk;
    }
}
