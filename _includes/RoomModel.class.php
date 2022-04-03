<?php

class RoomModel
{
    public ?int $room_id;
    public ?string $name;
    public ?string $no;
    public ?string $phone;

    public array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct($params = array())
    {
        $this->room_id = $params->room_id ?: null;
        $this->name = $params->name ?: null;
        $this->no = $params->no ?: null;
        $this->phone = $params->phone ?: null;
        $this->params = $params;
    }


    public function insert() : bool
    {
        try {
            $sql = "INSERT INTO room (name, no, phone) VALUES (:name, :no, :phone)";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':no', $this->no);
            $stmt->bindParam(':phone', $this->phone);
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function update() : bool
    {
        $sql = "UPDATE room SET name=:name, no=:no, phone=:phone WHERE room_id=:room_id";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':room_id', $this->room_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public static function getById($roomId)
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `room` WHERE `room_id`=:room_id");
        $stmt->bindParam(':room_id', $roomId);
        $stmt->execute();

        $record = $stmt->fetch();

        if (!$record) {
            return null;
        }

        return array(
        room_id => $record->room_id,
        name => $record->name,
        no => $record->no,
        phone => $record->phone
        );
    }

    public static function getAll($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `room` ORDER BY `{$orderBy}` {$orderDir}");
        $stmt->execute();
        return $stmt;
    }

    public static function deleteById(int $room_id) : bool
    {
        try {
            $sql = "DELETE FROM room WHERE room_id=:room_id";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindParam(':room_id', $room_id);
            return $stmt->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete() : bool
    {
        return self::deleteById($this->room_id);
    }


    public static function getFromPost() : self
    {
        $room = new self();
        $room->room_id = filter_input(INPUT_POST, "room_id", FILTER_VALIDATE_INT);
        $room->name = filter_input(INPUT_POST, "name");
        $room->no = filter_input(INPUT_POST, "no");
        $room->phone = filter_input(INPUT_POST, "phone");
        return $room;
    }

    public function validate() : bool
    {
        $isOk = true;
        $errors = [];

        if (!$this->name) {
            $isOk = false;
            $errors["name"] = "Nesmí být prázdný";
        }

        // if (!$this->no) {
        //     $isOk = false;
        //     $errors["no"] = "Nesmí být prázdný";
        // } elseif (!filter_var($this->no, FILTER_VALIDATE_INT)) {
        //     $errors["no"] = "Není číslo!";
        //     $this->no = null;
        //     $isOk = false;
        // }

        if ($this->phone === "") {
            $this->phone = null;
        }  elseif (!filter_var($this->phone, FILTER_VALIDATE_INT)) {
            $errors["phone"] = "Není číslo!";
            $this->phone = null;
            $isOk = false;
        }

        $this->validationErrors = $errors;
        return $isOk;
    }
}
