<?php

class KeyModel
{
    public ?int $key_id, $room_id, $employee_id;
    public array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function insert($employee_id, $room_id) : bool
    {
        try {
            $sql = "INSERT INTO `key` (`employee`, `room`) VALUES (:employee_id, :room_id)";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id);
            $stmt->bindParam(':room_id', $room_id);
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }


    public function getByEmployeeId($employeeId)
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `key` WHERE employee=:employee_id");
        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->execute();
        return $stmt;
    }

    public function getAll($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `key` ORDER BY `{$orderBy}` {$orderDir}");
        $stmt->execute();
        return $stmt;
    }

    public function deleteById(int $employee_id) : bool
    {
        $sql = "DELETE FROM `key` WHERE ((`employee` = :employee_id))";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee_id', $employee_id);
        return $stmt->execute();
    }

    public function delete() : bool
    {
        return self::deleteById($this->key_id);
    }


    public function getFromPost() : self
    {
        $room = new self();

        $room->key_id = filter_input(INPUT_POST, "key_id", FILTER_VALIDATE_INT);
        $room->room_id = filter_input(INPUT_POST, "room_id");
        $room->employee_id = filter_input(INPUT_POST, "employee_id");

        return $room;
    }

    public function validate() : bool
    {
        return true;

        $isOk = true;
        $errors = [];

        // if (!$this->name) {
        //     $isOk = false;
        //     $errors["name"] = "Nesmí být prázdný";
        // }
        //
        // if (!$this->no) {
        //     $isOk = false;
        //     $errors["no"] = "Nesmí být prázdný";
        // } elseif (!filter_var($this->no, FILTER_VALIDATE_INT)) {
        //     $errors["no"] = "Není číslo!";
        //     $this->no = null;
        //     $isOk = false;
        // }
        //
        // if ($this->phone === "") {
        //     $this->phone = null;
        // }  elseif (!filter_var($this->phone, FILTER_VALIDATE_INT)) {
        //     $errors["phone"] = "Není číslo!";
        //     $this->phone = null;
        //     $isOk = false;
        // }

        $this->validationErrors = $errors;
        return $isOk;
    }
}
