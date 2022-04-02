<?php

class RoomModelDetail extends RoomModel
{
    public function __construct($params = array())
    {
        parent::__construct();
        $this->avgWage = $params->avgWage;
        $this->params = $params;
    }

    public function getFromGet() : self
    {
        $room = new self();
        $room->room_id = filter_input(INPUT_GET, "room_id", FILTER_VALIDATE_INT);
        return $room;
    }

    public static function getById($roomId)
    {
        $model = parent::getById($roomId);

        $stmt = DB::getConnection()->prepare("SELECT ROUND(AVG(wage),2) as avgWage FROM employee WHERE room=:room_id");
        $stmt->bindParam(':room_id', $roomId);
        $stmt->execute();

        $record = $stmt->fetch();

        $model[avgWage] = $record->avgWage;

        $stmt = DB::getConnection()->prepare("SELECT CONCAT(surname, \" \",LEFT(name,1),\".\") AS name, employee_id FROM employee WHERE room=:room_id ORDER BY name");
        $stmt->bindParam(':room_id', $roomId);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $model[people][] = (object) array("id" => $row->employee_id, "name" => $row->name);
        }

        // 2. způsob SELECT CONCAT(surname, " ",LEFT(name,1),".") AS name, employee_id FROM employee WHERE employee_id IN (SELECT employee from `key` where room = {$roomId}) ORDER BY name
        $stmt = DB::getConnection()->prepare("SELECT CONCAT(surname, \" \",LEFT(name,1),\".\") AS name, employee_id FROM employee e JOIN `key` k ON e.employee_id = k.employee AND k.room=:room_id ORDER BY name");
        $stmt->bindParam(':room_id', $roomId);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $model[keys][] = array("id" => $row->employee_id, "name" => $row->name);
        }

        return new self($model);
    }

    public function validate() : bool
    {
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
